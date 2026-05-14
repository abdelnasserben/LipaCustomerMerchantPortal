<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class SendMoney extends Component
{
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
        $this->idempotencyKey = (string) \Str::uuid();
    }

    public function proceedToConfirm(): void
    {
        $this->error = '';
        if (empty($this->recipientPhone)) {
            $this->error = 'Please enter a recipient phone number.';
            return;
        }
        $amount = (int) str_replace([' ', ','], '', $this->amountInput);
        if ($amount < 1) {
            $this->error = 'Amount must be at least 1 KMF.';
            return;
        }
        $this->amount = $amount;

        $profile = MerchantData::profile();
        if (!$profile['canReceiveFromMerchant']) {
            $this->error = 'M2M transfers are not enabled for your account.';
            return;
        }

        $this->step = 'confirm';
    }

    public function submit(): void
    {
        $this->error = '';
        $fee = (int) ($this->amount * 0.01);
        $net = $this->amount - $fee;
        $this->receipt = [
            'transactionId' => 'tx_m2m_' . substr(md5(uniqid()), 0, 8),
            'status' => 'COMPLETED',
            'requestedAmount' => $this->amount,
            'feeAmount' => $fee,
            'netAmountToDestination' => $net,
            'completedAt' => now()->toIso8601String(),
            'replayed' => false,
        ];
        $this->step = 'receipt';
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
        $this->idempotencyKey = (string) \Str::uuid();
    }

    public function render()
    {
        $profile = MerchantData::profile();
        $balance = MerchantData::balance();
        return view('livewire.merchant.send-money', compact('profile', 'balance'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Send Money']);
    }
}
