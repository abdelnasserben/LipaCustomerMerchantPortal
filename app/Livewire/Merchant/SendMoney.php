<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Exceptions\KomopayException;
use App\Komopay\Support\IdempotencyKey;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class SendMoney extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public string $step = 'form'; // form | confirm | receipt
    public string $recipientPhone = '';
    public string $recipientCountryCode = '269';
    public int $amount = 0;
    public string $amountInput = '';
    public string $description = '';
    public string $idempotencyKey = '';
    public string $error = '';
    public ?array $receipt = null;

    public function mount(): void
    {
        $this->recipientCountryCode = (string) config('komopay.default_country_code');
        $this->idempotencyKey = IdempotencyKey::generate();
    }

    public function proceedToConfirm(MerchantApi $api): void
    {
        $this->error = '';
        if (empty($this->recipientPhone)) {
            $this->error = __('merchant.errors.recipient_required');
            return;
        }
        $amount = (int) str_replace([' ', ','], '', $this->amountInput);
        if ($amount < 1) {
            $this->error = __('merchant.errors.amount_min_1');
            return;
        }
        $this->amount = $amount;

        // Spec 9.3: gate UI on canReceiveFromMerchant; backend remains authoritative.
        $profile = $api->profile();
        if (empty($profile['canReceiveFromMerchant'])) {
            $this->error = __('merchant.errors.m2m_not_enabled');
            return;
        }

        $this->step = 'confirm';
    }

    public function submit(MerchantApi $api): void
    {
        $this->error = '';
        try {
            $this->receipt = $api->m2mTransfer([
                'recipientCountryCode' => $this->recipientCountryCode,
                'recipientPhone'       => str_replace(' ', '', $this->recipientPhone),
                'amount'               => $this->amount,
                'description'          => $this->description ?: null,
            ], $this->idempotencyKey);
            $this->step = 'receipt';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function resetForm(): void
    {
        $this->step = 'form';
        $this->recipientPhone = '';
        $this->amount = 0;
        $this->amountInput = '';
        $this->description = '';
        $this->error = '';
        $this->receipt = null;
        $this->idempotencyKey = IdempotencyKey::generate();
    }

    public function render(MerchantApi $api)
    {
        $profile = $api->profile();
        $balance = $api->balance();
        return view('livewire.merchant.send-money', compact('profile', 'balance'))
            ->layout('layouts.merchant', ['title' => __('merchant.title.send_money')]);
    }
}
