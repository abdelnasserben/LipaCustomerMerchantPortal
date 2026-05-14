<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class Security extends Component
{
    public string $activePanel = '';
    public string $currentPin = '';
    public string $newPin = '';
    public string $confirmPin = '';
    public string $totpCode = '';
    public bool $totpEnrolled = false;
    public string $totpSecret = 'MFZWI3DZEBVGK3TF';
    public string $error = '';
    public string $success = '';

    public function openPanel(string $panel): void
    {
        $this->activePanel = $panel;
        $this->error = $this->success = '';
    }

    public function changePin(): void
    {
        $this->error = '';
        if (strlen($this->newPin) < 4 || strlen($this->newPin) > 8) {
            $this->error = 'New PIN must be 4–8 digits.';
            return;
        }
        if ($this->newPin !== $this->confirmPin) {
            $this->error = 'PINs do not match.';
            return;
        }
        $this->success = 'PIN changed successfully.';
        $this->currentPin = $this->newPin = $this->confirmPin = '';
        $this->activePanel = '';
    }

    public function enrollTotp(): void
    {
        $this->error = '';
        if (strlen($this->totpCode) !== 6) {
            $this->error = 'Enter the 6-digit code from your authenticator app.';
            return;
        }
        $this->totpEnrolled = true;
        $this->success = 'Two-factor authentication enabled.';
        $this->activePanel = '';
    }

    public function revokeTotp(): void
    {
        $this->error = '';
        if (strlen($this->totpCode) !== 6) {
            $this->error = 'Enter the 6-digit code to confirm.';
            return;
        }
        $this->totpEnrolled = false;
        $this->success = 'Two-factor authentication disabled.';
        $this->activePanel = '';
    }

    public function render()
    {
        $profile = MerchantData::profile();
        return view('livewire.merchant.security', compact('profile'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Security']);
    }
}
