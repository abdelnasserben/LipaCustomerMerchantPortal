<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Contracts\MerchantAuthApi;
use App\Komopay\Exceptions\KomopayException;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Security extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public string $activePanel = '';
    public string $currentPin = '';
    public string $newPin = '';
    public string $confirmPin = '';
    public string $totpCode = '';
    public bool $totpEnrolled = false;
    public string $totpSecret = '';
    public string $totpQrUri = '';
    public string $error = '';
    public string $success = '';

    public function openPanel(string $panel, MerchantAuthApi $auth): void
    {
        $this->activePanel = $panel;
        $this->error = $this->success = '';

        if ($panel === 'enrollTOTP' && $this->totpSecret === '') {
            try {
                $token = app('komopay.tokens.merchant')->accessToken() ?? '';
                $setup = $auth->totpSetup($token);
                $this->totpSecret = $setup['secret'] ?? '';
                $this->totpQrUri  = $setup['qrUri']  ?? '';
            } catch (KomopayException $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    public function changePin(MerchantAuthApi $auth): void
    {
        $this->error = '';
        if ($this->newPin !== $this->confirmPin) {
            $this->error = 'PINs do not match.';
            return;
        }
        try {
            $token = app('komopay.tokens.merchant')->accessToken() ?? '';
            $auth->changePin($token, $this->currentPin, $this->newPin);
            $this->success = 'PIN changed successfully.';
            $this->currentPin = $this->newPin = $this->confirmPin = '';
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function enrollTotp(MerchantAuthApi $auth): void
    {
        $this->error = '';
        try {
            $token = app('komopay.tokens.merchant')->accessToken() ?? '';
            $auth->totpConfirm($token, $this->totpCode);
            $this->totpEnrolled = true;
            $this->success = 'Two-factor authentication enabled.';
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function revokeTotp(MerchantAuthApi $auth): void
    {
        $this->error = '';
        try {
            $token = app('komopay.tokens.merchant')->accessToken() ?? '';
            $auth->totpRevoke($token, $this->totpCode);
            $this->totpEnrolled = false;
            $this->success = 'Two-factor authentication disabled.';
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render(MerchantApi $api)
    {
        $profile = $api->profile();
        return view('livewire.merchant.security', compact('profile'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Security']);
    }
}
