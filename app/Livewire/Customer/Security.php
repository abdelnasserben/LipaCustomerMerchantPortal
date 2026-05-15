<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Exceptions\KomopayException;
use App\Komopay\Support\TokenStore;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Security extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public string $activePanel = ''; // '' | changePIN | enrollTOTP | revokeTOTP
    public string $currentPin = '';
    public string $newPin = '';
    public string $confirmPin = '';
    public string $totpCode = '';
    public bool $totpEnrolled = false;
    public string $totpSecret = '';
    public string $totpQrUri = '';
    public string $error = '';
    public string $success = '';

    public function openPanel(string $panel, CustomerAuthApi $auth): void
    {
        $this->activePanel = $panel;
        $this->error = $this->success = '';

        if ($panel === 'enrollTOTP' && $this->totpSecret === '') {
            try {
                $token = app('komopay.tokens.customer')->accessToken() ?? '';
                $setup = $auth->totpSetup($token);
                $this->totpSecret = $setup['secret'] ?? '';
                $this->totpQrUri  = $setup['qrUri']  ?? '';
            } catch (KomopayException $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    public function changePin(CustomerAuthApi $auth): void
    {
        $this->error = '';
        if ($this->newPin !== $this->confirmPin) {
            $this->error = 'PINs do not match.';
            return;
        }
        try {
            $token = app('komopay.tokens.customer')->accessToken() ?? '';
            $auth->changePin($token, $this->currentPin, $this->newPin);
            $this->success = 'PIN changed successfully.';
            $this->currentPin = $this->newPin = $this->confirmPin = '';
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function enrollTotp(CustomerAuthApi $auth): void
    {
        $this->error = '';
        try {
            $token = app('komopay.tokens.customer')->accessToken() ?? '';
            $auth->totpConfirm($token, $this->totpCode);
            $this->totpEnrolled = true;
            $this->success = 'Two-factor authentication enabled.';
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function revokeTotp(CustomerAuthApi $auth): void
    {
        $this->error = '';
        try {
            $token = app('komopay.tokens.customer')->accessToken() ?? '';
            $auth->totpRevoke($token, $this->totpCode);
            $this->totpEnrolled = false;
            $this->success = 'Two-factor authentication disabled.';
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render(CustomerApi $api)
    {
        $profile = $api->profile();
        return view('livewire.customer.security', compact('profile'))
            ->layout('layouts.customer', ['title' => 'Lipa · Security']);
    }
}
