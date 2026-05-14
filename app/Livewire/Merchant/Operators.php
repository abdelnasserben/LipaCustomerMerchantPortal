<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class Operators extends Component
{
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

    // Local state (simulated)
    public array $operators = [];

    public function mount(): void
    {
        $this->operators = MerchantData::operators();
    }

    public function createOperator(): void
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
        // Check for duplicate phone
        foreach ($this->operators as $op) {
            if ($op['phoneNumber'] === str_replace(' ', '', $this->phoneNumber)) {
                $this->error = 'This phone number is already in use (PHONE_ALREADY_IN_USE).';
                return;
            }
        }

        $newOp = [
            'id' => 'op_new_' . uniqid(),
            'merchantId' => 'mer_27c9e1ab',
            'fullName' => $this->fullName,
            'phoneCountryCode' => '269',
            'phoneNumber' => str_replace(' ', '', $this->phoneNumber),
            'status' => 'ACTIVE',
            'createdAt' => now()->toIso8601String(),
            'lastLoginAt' => null,
        ];

        $this->operators[] = $newOp;
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

    public function confirmAction(): void
    {
        foreach ($this->operators as &$op) {
            if ($op['id'] === $this->actionOperatorId) {
                $op['status'] = match ($this->actionType) {
                    'suspend'    => 'SUSPENDED',
                    'reactivate' => 'ACTIVE',
                    'revoke'     => 'REVOKED',
                    default      => $op['status'],
                };
                break;
            }
        }
        $this->showActionModal = false;
        $this->actionOperatorId = null;
        $this->actionType = '';
    }

    public function render()
    {
        $operators = $this->operators;
        if ($this->filterStatus) {
            $operators = array_filter($operators, fn($o) => $o['status'] === $this->filterStatus);
        }
        return view('livewire.merchant.operators', ['operators' => array_values($operators)])
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Cashiers']);
    }
}
