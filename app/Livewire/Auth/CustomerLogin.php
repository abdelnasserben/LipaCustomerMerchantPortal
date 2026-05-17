<?php

namespace App\Livewire\Auth;

use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Exceptions\BusinessException;
use App\Komopay\Exceptions\KomopayException;
use Livewire\Component;

class CustomerLogin extends Component
{
    public string $step = 'login'; // login | mfa | pinSetup | resetPin | resetPinDone | sessionExpired | locked
    public string $phoneCountryCode = '269';
    public string $phoneNumber = '';
    public string $pin = '';
    public bool $pinVisible = false;
    public string $mfaCode = '';
    public string $newPin = '';
    public string $confirmPin = '';
    public string $resetTotpCode = '';
    public string $error = '';

    public ?string $challengeId = null;
    public ?string $pinSetupToken = null;

    public function mount(string $step = 'login'): void
    {
        $this->step = request()->query('step', $step);
        $this->phoneCountryCode = (string) config('komopay.default_country_code');
    }

    public function login(CustomerAuthApi $auth): void
    {
        $this->error = '';
        if (empty($this->phoneNumber) || empty($this->pin)) {
            $this->error = __('auth.errors.phone_pin_required');
            return;
        }

        try {
            $resp = $auth->login($this->phoneCountryCode, $this->phoneNumber, $this->pin);
        } catch (BusinessException $e) {
            // Spec 12.1 — surface lock / KYC / suspension states.
            $this->step = $e->errorCode() === 'AUTH_PIN_LOCKED' ? 'locked' : 'login';
            $this->error = $e->getMessage();
            return;
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
            return;
        }

        if (!empty($resp['pinSetupRequired'])) {
            $this->pinSetupToken = $resp['pinSetupToken'] ?? null;
            $this->step = 'pinSetup';
            return;
        }
        if (!empty($resp['mfaRequired'])) {
            $this->challengeId = $resp['challengeId'] ?? null;
            $this->step = 'mfa';
            return;
        }
        if (!empty($resp['tokens'])) {
            app('komopay.tokens.customer')->put($resp['tokens']);
            session(['actor_type' => 'customer', 'auth_user' => ['name' => 'Customer', 'type' => 'customer']]);
            $this->redirect(route('customer.dashboard'), navigate: true);
        }
    }

    public function verifyMfa(CustomerAuthApi $auth): void
    {
        $this->error = '';
        if (strlen($this->mfaCode) !== 6) {
            $this->error = __('auth.errors.mfa_code_required');
            return;
        }
        try {
            $resp = $auth->verifyMfa((string) $this->challengeId, $this->mfaCode);
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
            return;
        }
        if (!empty($resp['tokens'])) {
            app('komopay.tokens.customer')->put($resp['tokens']);
            session(['actor_type' => 'customer', 'auth_user' => ['name' => 'Customer', 'type' => 'customer']]);
            $this->redirect(route('customer.dashboard'), navigate: true);
        }
    }

    public function startReset(): void
    {
        $this->error = '';
        $this->resetTotpCode = '';
        $this->newPin = $this->confirmPin = '';
        $this->step = 'resetPin';
    }

    public function resetPin(CustomerAuthApi $auth): void
    {
        $this->error = '';
        if (empty($this->phoneNumber)) {
            $this->error = __('auth.errors.phone_required');
            return;
        }
        if (strlen($this->resetTotpCode) !== 6) {
            $this->error = __('auth.errors.mfa_code_required');
            return;
        }
        if (strlen($this->newPin) < 4 || strlen($this->newPin) > 8) {
            $this->error = __('auth.errors.pin_length');
            return;
        }
        if ($this->newPin !== $this->confirmPin) {
            $this->error = __('auth.errors.pins_dont_match');
            return;
        }
        try {
            $auth->resetPin($this->phoneCountryCode, $this->phoneNumber, $this->resetTotpCode, $this->newPin);
        } catch (BusinessException $e) {
            // AUTH_PIN_RESET_TOTP_REQUIRED, ACTOR_* — surface message, stay on form
            $this->error = $e->getMessage();
            return;
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
            return;
        }
        $this->resetTotpCode = '';
        $this->newPin = $this->confirmPin = '';
        $this->pin = '';
        $this->step = 'resetPinDone';
    }

    public function setupPin(CustomerAuthApi $auth): void
    {
        $this->error = '';
        if (strlen($this->newPin) < 4 || strlen($this->newPin) > 8) {
            $this->error = __('auth.errors.pin_length');
            return;
        }
        if ($this->newPin !== $this->confirmPin) {
            $this->error = __('auth.errors.pins_dont_match');
            return;
        }
        try {
            $auth->setupPin((string) $this->pinSetupToken, $this->newPin);
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
            return;
        }
        $this->step = 'login';
        $this->newPin = $this->confirmPin = '';
        $this->pinSetupToken = null;
    }

    public function render()
    {
        return view('livewire.auth.customer-login')
            ->layout('layouts.auth');
    }
}
