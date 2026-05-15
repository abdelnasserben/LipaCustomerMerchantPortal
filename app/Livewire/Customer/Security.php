<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Exceptions\KomopayException;
use App\Livewire\Concerns\HandlesAuthException;
use App\Services\QrCodeService;
use Livewire\Component;

class Security extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    private const SESSION_KEY = 'customer_totp_enrolled';

    public string $activePanel = ''; // '' | changePIN | enrollTOTP | revokeTOTP
    public string $currentPin = '';
    public string $newPin = '';
    public string $confirmPin = '';
    public string $totpCode = '';
    public bool $totpEnrolled = false;
    public string $totpSecret = '';
    public string $totpQrUri = '';
    public string $totpQrSvg = '';
    public string $error = '';
    public string $success = '';

    public function mount(): void
    {
        $this->totpEnrolled = (bool) session(self::SESSION_KEY, false);
    }

    public function openPanel(string $panel, CustomerAuthApi $auth): void
    {
        $this->activePanel = $panel;
        $this->error = $this->success = '';
        $this->totpCode = '';

        if ($panel === '') {
            return;
        }

        if ($panel === 'enrollTOTP' && $this->totpSecret === '') {
            try {
                $token = app('komopay.tokens.customer')->accessToken() ?? '';
                $setup = $auth->totpSetup($token);
                $this->totpSecret = $setup['secret'] ?? '';
                $this->totpQrUri  = $setup['qrUri']  ?? '';
                $this->totpQrSvg  = $this->totpQrUri !== '' ? QrCodeService::svg($this->totpQrUri, 200) : '';
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
        if (strlen($this->totpCode) !== 6) {
            $this->error = 'Enter the 6-digit code from your authenticator app.';
            return;
        }
        try {
            $token = app('komopay.tokens.customer')->accessToken() ?? '';
            $auth->totpConfirm($token, $this->totpCode);
            $this->totpEnrolled = true;
            session([self::SESSION_KEY => true]);
            $this->success = 'Two-factor authentication enabled.';
            $this->resetTotpState();
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function revokeTotp(CustomerAuthApi $auth): void
    {
        $this->error = '';
        if (strlen($this->totpCode) !== 6) {
            $this->error = 'Enter your current 6-digit code.';
            return;
        }
        try {
            $token = app('komopay.tokens.customer')->accessToken() ?? '';
            $auth->totpRevoke($token, $this->totpCode);
            $this->totpEnrolled = false;
            session()->forget(self::SESSION_KEY);
            $this->success = 'Two-factor authentication disabled.';
            $this->resetTotpState();
            $this->activePanel = '';
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
    }

    private function resetTotpState(): void
    {
        $this->totpCode = '';
        $this->totpSecret = '';
        $this->totpQrUri = '';
        $this->totpQrSvg = '';
    }

    public function render(CustomerApi $api)
    {
        $profile = $api->profile();
        return view('livewire.customer.security', compact('profile'))
            ->layout('layouts.customer', ['title' => 'Lipa · Security']);
    }
}
