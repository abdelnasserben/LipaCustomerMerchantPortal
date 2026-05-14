<?php

namespace App\Livewire\Customer;

use App\Data\Mock\CustomerData;
use App\Services\FormatService;
use Livewire\Component;
use Ramsey\Uuid\Uuid;

class SendMoney extends Component
{
    public string $step = 'recipient'; // recipient | amount | confirm | pin | threshold | receipt
    public string $recipientPhone = '';
    public string $recipientCountryCode = '269';
    public string $recipientName = '';
    public int $amount = 0;
    public string $amountInput = '';
    public string $description = '';
    public string $pin = '';
    public string $error = '';
    public string $idempotencyKey = '';
    public bool $showBeneficiaries = true;

    // Receipt data
    public ?array $receipt = null;
    // Control gate
    public ?int $thresholdAmount = null;

    public function mount(): void
    {
        $this->idempotencyKey = (string) \Str::uuid();
    }

    public function selectBeneficiary(string $phone, string $name): void
    {
        $this->recipientPhone = $phone;
        $this->recipientName = $name;
    }

    public function proceedToAmount(): void
    {
        $this->error = '';
        if (empty($this->recipientPhone)) {
            $this->error = 'Please enter a recipient phone number.';
            return;
        }
        // Lookup name from beneficiaries
        if (empty($this->recipientName)) {
            foreach (CustomerData::beneficiaries() as $b) {
                if ($b['phoneNumber'] === str_replace(' ', '', $this->recipientPhone)) {
                    $this->recipientName = $b['fullName'];
                    break;
                }
            }
            if (empty($this->recipientName)) {
                $this->recipientName = '+269 ' . $this->recipientPhone;
            }
        }
        $this->step = 'amount';
    }

    public function proceedToConfirm(): void
    {
        $this->error = '';
        $amount = (int) str_replace([' ', ','], '', $this->amountInput);
        if ($amount < 100) {
            $this->error = 'Minimum amount is 100 KMF.';
            return;
        }
        if ($amount > 500000) {
            $this->error = 'Maximum single transaction is 500 000 KMF.';
            return;
        }
        $this->amount = $amount;
        $this->step = 'confirm';
    }

    public function submitTransfer(): void
    {
        $this->error = '';
        // Simulate control gate: amounts > 50 000 KMF trigger PENDING_CONFIRMATION
        if ($this->amount > 50000 && $this->step === 'confirm') {
            $this->thresholdAmount = 50000;
            $this->step = 'threshold';
            return;
        }
        // Simulate PIN step for confirm amounts 10 000–50 000
        if ($this->amount >= 10000 && $this->step === 'confirm') {
            $this->step = 'pin';
            return;
        }
        $this->executeTransfer();
    }

    public function submitWithPin(): void
    {
        $this->error = '';
        if (strlen($this->pin) < 4) {
            $this->error = 'Enter your PIN to confirm.';
            return;
        }
        $this->executeTransfer();
    }

    public function confirmThreshold(): void
    {
        $this->step = 'pin';
    }

    private function executeTransfer(): void
    {
        $fee = (int) ($this->amount * 0.01);
        $net = $this->amount - $fee;
        $this->receipt = [
            'transactionId' => 'tx_' . substr(md5(uniqid()), 0, 10),
            'outcome' => 'EXECUTED',
            'requestedAmount' => $this->amount,
            'feeAmount' => $fee,
            'netAmountToDestination' => $net,
            'currency' => 'KMF',
            'completedAt' => now()->toIso8601String(),
            'replayed' => false,
            'recipientName' => $this->recipientName,
        ];
        $this->step = 'receipt';
    }

    public function resetForm(): void
    {
        $this->step = 'recipient';
        $this->recipientPhone = '';
        $this->recipientName = '';
        $this->amount = 0;
        $this->amountInput = '';
        $this->description = '';
        $this->pin = '';
        $this->error = '';
        $this->receipt = null;
        $this->idempotencyKey = (string) \Str::uuid();
    }

    public function render()
    {
        $beneficiaries = CustomerData::beneficiaries();
        $balance = CustomerData::balance();
        return view('livewire.customer.send-money', compact('beneficiaries', 'balance'))
            ->layout('layouts.customer', ['title' => 'Lipa · Send Money']);
    }
}
