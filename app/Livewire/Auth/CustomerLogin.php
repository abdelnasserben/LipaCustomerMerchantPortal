<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class CustomerLogin extends Component
{
    public string $step = 'login'; // login | mfa | pinSetup | sessionExpired | locked
    public string $phoneNumber = '';
    public string $pin = '';
    public bool $pinVisible = false;
    public string $mfaCode = '';
    public string $newPin = '';
    public string $confirmPin = '';
    public string $error = '';

    public function mount(string $step = 'login'): void
    {
        $this->step = $step;
    }

    public function login(): void
    {
        $this->error = '';
        if (empty($this->phoneNumber) || empty($this->pin)) {
            $this->error = 'Please enter your phone number and PIN.';
            return;
        }
        // Simulate API response branches
        if ($this->pin === '0000') {
            $this->step = 'locked';
            return;
        }
        if ($this->pin === '2222') {
            $this->step = 'mfa';
            return;
        }
        if ($this->pin === '3333') {
            $this->step = 'pinSetup';
            return;
        }
        // Success — redirect to customer dashboard
        session(['actor_type' => 'customer', 'auth_user' => ['name' => 'Saïd Ahamadi', 'type' => 'customer']]);
        $this->redirect(route('customer.dashboard'), navigate: true);
    }

    public function verifyMfa(): void
    {
        $this->error = '';
        if (strlen($this->mfaCode) !== 6) {
            $this->error = 'Enter the 6-digit code from your authenticator app.';
            return;
        }
        session(['actor_type' => 'customer', 'auth_user' => ['name' => 'Saïd Ahamadi', 'type' => 'customer']]);
        $this->redirect(route('customer.dashboard'), navigate: true);
    }

    public function setupPin(): void
    {
        $this->error = '';
        if (strlen($this->newPin) < 4 || strlen($this->newPin) > 8) {
            $this->error = 'PIN must be 4 to 8 digits.';
            return;
        }
        if ($this->newPin !== $this->confirmPin) {
            $this->error = 'PINs do not match.';
            return;
        }
        $this->step = 'login';
        $this->newPin = '';
        $this->confirmPin = '';
    }

    public function render()
    {
        return view('livewire.auth.customer-login')
            ->layout('layouts.auth');
    }
}
