<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Exceptions\KomopayException;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Operators extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public string $filterStatus = '';
    public bool $showCreateDrawer = false;
    public ?string $actionOperatorId = null;
    public string $actionType = ''; // suspend | reactivate | revoke
    public bool $showActionModal = false;

    // Create form
    public string $fullName = '';
    public string $phoneNumber = '';
    public string $pin = '';
    public string $confirmPin = '';
    public string $error = '';
    public string $success = '';
    public ?array $createdOperatorPin = null;

    public function createOperator(MerchantApi $api): void
    {
        $this->error = '';
        if (empty($this->fullName) || empty($this->phoneNumber) || empty($this->pin)) {
            $this->error = 'All fields are required.';
            return;
        }
        if (strlen($this->pin) < 4 || strlen($this->pin) > 8) {
            $this->error = 'PIN must be 4–8 digits.';
            return;
        }
        if ($this->pin !== $this->confirmPin) {
            $this->error = 'PINs do not match.';
            return;
        }

        try {
            $newOp = $api->createOperator([
                'fullName'         => $this->fullName,
                'phoneCountryCode' => (string) config('komopay.default_country_code'),
                'phoneNumber'      => str_replace(' ', '', $this->phoneNumber),
                'pin'              => $this->pin,
            ]);
        } catch (KomopayException $e) {
            // PHONE_ALREADY_IN_USE → inline form error (spec 12.2).
            $this->error = $e->errorCode() === 'PHONE_ALREADY_IN_USE'
                ? 'This phone number is already in use (PHONE_ALREADY_IN_USE).'
                : $e->getMessage();
            return;
        }

        $this->createdOperatorPin = ['operator' => $newOp, 'pin' => $this->pin];
        $this->showCreateDrawer = false;
        $this->fullName = $this->phoneNumber = $this->pin = $this->confirmPin = '';
        $this->success = "Cashier {$newOp['fullName']} created. Share the PIN securely — it cannot be retrieved later.";
    }

    public function openAction(string $id, string $type): void
    {
        $this->actionOperatorId = $id;
        $this->actionType = $type;
        $this->showActionModal = true;
    }

    public function confirmAction(MerchantApi $api): void
    {
        if (!$this->actionOperatorId) return;
        try {
            match ($this->actionType) {
                'suspend'    => $api->suspendOperator($this->actionOperatorId),
                'reactivate' => $api->reactivateOperator($this->actionOperatorId),
                'revoke'     => $api->revokeOperator($this->actionOperatorId),
                default      => null,
            };
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
        $this->showActionModal = false;
        $this->actionOperatorId = null;
        $this->actionType = '';
    }

    public function render(MerchantApi $api)
    {
        $operators = [];
        try {
            $operators = $api->operators(
                status: $this->filterStatus ?: null,
                limit: 100,
            )->items;
        } catch (KomopayException) {
            // Treat backend errors as empty list — empty state handles UX.
        }

        return view('livewire.merchant.operators', ['operators' => array_values($operators)])
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Cashiers']);
    }
}
