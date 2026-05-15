<div class="min-h-screen flex flex-col lg:flex-row" style="background: var(--color-bg);">
    {{-- Mobile/tablet: dark hero header  |  Desktop (lg+): left panel --}}
    <div class="relative overflow-hidden lg:w-1/2 lg:flex lg:flex-col lg:justify-between"
         style="background: #0a0a0a;">
        <div class="grid-bg"></div>

        {{-- Mobile/tablet hero (compact, like customer) --}}
        <div class="relative px-6 sm:px-10 pt-12 pb-10 text-white lg:hidden">
            <div class="max-w-md mx-auto sm:max-w-xl">
                <div class="flex items-center gap-3 mb-8">
                    <x-lipa-mark :size="44" :dark="true"/>
                    <div>
                        <div class="font-bold" style="font-size: 20px; letter-spacing: -0.01em;">Lipa</div>
                        <div class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.5); margin-top: 2px;">Merchant Portal</div>
                    </div>
                </div>
                <h1 style="font-size: clamp(26px, 5vw, 34px); font-weight: 700; letter-spacing: -0.025em; line-height: 1.05; margin: 0;">
                    @if($step === 'login') Sign in to Lipa
                    @elseif($step === 'mfa') Two-factor verification
                    @elseif($step === 'pinSetup') Set your PIN
                    @elseif($step === 'sessionExpired') Session expired
                    @elseif($step === 'locked') PIN locked
                    @endif
                </h1>
                <p style="font-size: 14px; color: rgba(255,255,255,0.6); margin-top: 10px; line-height: 1.5; max-width: 360px;">
                    @if($step === 'login') Use your registered phone number and PIN.
                    @elseif($step === 'mfa') Open your authenticator app and enter the 6-digit code.
                    @elseif($step === 'pinSetup') Choose a 4–8 digit PIN. Required for every transfer.
                    @elseif($step === 'sessionExpired') Merchant sessions last 8 hours. Sign in again to continue.
                    @elseif($step === 'locked') Too many incorrect attempts. Try again in 14 minutes.
                    @endif
                </p>
            </div>
            <div style="position: absolute; bottom: -1px; left: 0; right: 0; height: 24px; background: var(--color-bg); border-radius: 24px 24px 0 0;"></div>
        </div>

        {{-- Desktop left panel --}}
        <div class="relative hidden lg:flex lg:flex-col lg:justify-between" style="padding: 48px 56px; min-height: 100vh;">
            <div class="relative">
                <div class="flex items-center gap-3">
                    <x-lipa-mark :size="48" :dark="true"/>
                    <div>
                        <div class="font-bold text-white" style="font-size: 22px; letter-spacing: -0.01em;">Lipa</div>
                        <div class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.45); margin-top: 2px;">Merchant Portal</div>
                    </div>
                </div>
            </div>
            <div class="relative">
                <h1 class="text-white" style="font-size: 48px; font-weight: 700; line-height: 1.05; letter-spacing: -0.03em; max-width: 420px; margin: 0;">
                    Run your business.<br>Settle in seconds.
                </h1>
                <p style="color: rgba(255,255,255,0.6); font-size: 15px; line-height: 1.55; margin-top: 18px; max-width: 380px;">
                    Manage payments, terminals and your cashier team — securely, from one place.
                </p>
            </div>
            <div class="relative" style="font-size: 12px; color: rgba(255,255,255,0.35);">
                Lipa Merchant Portal · All sessions are logged and monitored
            </div>
        </div>
    </div>

    {{-- Form area --}}
    <div class="flex-1 flex lg:items-center justify-center px-6 sm:px-10 pt-6 pb-10 lg:px-16 lg:py-10" style="background: var(--color-bg);">
        <div class="w-full max-w-md sm:max-w-lg lg:max-w-md">
            {{-- Desktop-only heading (mobile uses the dark hero above) --}}
            <div class="hidden lg:block mb-8">
                <h2 style="font-size: 28px; font-weight: 700; letter-spacing: -0.025em; margin: 0; color: var(--color-ink-hi);">
                    @if($step === 'login') Sign in to Lipa
                    @elseif($step === 'mfa') Two-factor verification
                    @elseif($step === 'pinSetup') Set your PIN
                    @elseif($step === 'sessionExpired') Session expired
                    @elseif($step === 'locked') PIN locked
                    @endif
                </h2>
                <p style="font-size: 14.5px; color: var(--color-ink-mid); margin-top: 8px; line-height: 1.5;">
                    @if($step === 'login') Use your registered phone number and PIN.
                    @elseif($step === 'mfa') Open your authenticator app and enter the 6-digit code.
                    @elseif($step === 'pinSetup') Choose a 4–8 digit PIN. Required for every transfer.
                    @elseif($step === 'sessionExpired') Merchant sessions last 8 hours. Sign in again to continue.
                    @elseif($step === 'locked') Too many incorrect attempts. Try again in 14 minutes.
                    @endif
                </p>
            </div>

            @if($error)
            <div class="alert alert-danger mb-5">
                <x-icon name="warn" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
                <span>{{ $error }}</span>
            </div>
            @endif

            @if($step === 'login')
            <form wire:submit="login" class="flex flex-col gap-5">
                <div>
                    <label class="label">Phone Number</label>
                    <div style="display: flex; height: 52px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; overflow: hidden;">
                        <div style="width: 80px; background: var(--color-surface-alt); display: flex; align-items: center; justify-content: center; border-right: 1px solid var(--color-border); font-family: var(--font-mono); font-weight: 600; font-size: 15px; color: var(--color-ink-mid); flex-shrink: 0;">
                            +269
                        </div>
                        <input wire:model="phoneNumber" type="tel" placeholder="33 XX XX XX" inputmode="tel"
                            style="flex: 1; min-width: 0; border: none; outline: none; padding: 0 16px; font-family: var(--font-mono); font-size: 16px; color: var(--color-ink-hi); background: transparent; letter-spacing: 0.04em;"/>
                    </div>
                </div>
                <div>
                    <label class="label">PIN</label>
                    <div style="display: flex; height: 52px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; align-items: center; padding: 0 16px; gap: 8px;">
                        <input wire:model="pin" type="{{ $pinVisible ? 'text' : 'password' }}" placeholder="••••" inputmode="numeric"
                            style="flex: 1; min-width: 0; border: none; outline: none; font-family: var(--font-mono); font-size: 20px; color: var(--color-ink-hi); background: transparent; letter-spacing: 0.3em;"/>
                        <button type="button" wire:click="$toggle('pinVisible')" style="background: none; border: none; cursor: pointer; color: var(--color-ink-low); padding: 4px; display: flex;">
                            @if($pinVisible)<x-icon name="eye-off" class="w-5 h-5"/>@else<x-icon name="eye" class="w-5 h-5"/>@endif
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark btn-lg btn-full">Sign In</button>
            </form>
            @endif

            @if($step === 'mfa')
            <form wire:submit="verifyMfa" class="flex flex-col gap-6">
                <div x-data="otpInput(@js($mfaCode))" x-init="$watch('value', v => $wire.set('mfaCode', v, false))">
                    <label class="label">Authenticator Code</label>
                    <div class="flex gap-2 justify-between" x-on:paste.prevent="onPaste($event)">
                        @for($i = 0; $i < 6; $i++)
                        <input type="text" maxlength="1" inputmode="numeric" autocomplete="one-time-code"
                            class="otp-input flex-1 min-w-0"
                            x-ref="d{{ $i }}"
                            x-on:input="onInput($event, {{ $i }})"
                            x-on:keydown="onKeydown($event, {{ $i }})"
                            x-on:focus="$event.target.select()"
                            @if($i === 0) autofocus @endif />
                        @endfor
                    </div>
                </div>
                <div class="alert alert-info">
                    <x-icon name="shield" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
                    <span>Open your authenticator app and enter the code.</span>
                </div>
                <button type="submit" class="btn btn-dark btn-lg btn-full">
                    Verify Code
                </button>
                <button type="button" wire:click="$set('step', 'login')" class="btn btn-ghost btn-md btn-full" style="color: var(--color-ink-mid);">
                    ← Back
                </button>
            </form>
            @endif

            @if($step === 'pinSetup')
            <form wire:submit="setupPin" class="flex flex-col gap-5">
                <div>
                    <label class="label">New PIN</label>
                    <input wire:model="newPin" type="password" placeholder="4–8 digits" class="input" inputmode="numeric"/>
                </div>
                <div>
                    <label class="label">Confirm PIN</label>
                    <input wire:model="confirmPin" type="password" placeholder="Repeat your PIN" class="input" inputmode="numeric"/>
                </div>
                <button type="submit" class="btn btn-dark btn-lg btn-full">Set PIN & Continue</button>
            </form>
            @endif

            @if($step === 'sessionExpired')
            <div class="flex flex-col gap-5">
                <div class="alert alert-warn">
                    <x-icon name="warn" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
                    <div>
                        <div class="font-semibold mb-1">Session expired</div>
                        <div>Merchant sessions last 8 hours. There is no automatic refresh — please sign in again to continue.</div>
                    </div>
                </div>
                <button wire:click="$set('step', 'login')" class="btn btn-dark btn-lg btn-full">Sign In Again</button>
            </div>
            @endif

            @if($step === 'locked')
            <div class="flex flex-col gap-5">
                <div class="alert alert-danger">
                    <x-icon name="lock" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
                    <div>
                        <div class="font-semibold mb-1">PIN locked for 15 minutes</div>
                        <div>After 3 failed attempts, PIN entry is temporarily blocked.</div>
                    </div>
                </div>
                <button wire:click="$set('step', 'login')" class="btn btn-secondary btn-lg btn-full">Try Again Later</button>
            </div>
            @endif

            <div class="mt-8 text-center">
                <a wire:navigate href="{{ route('customer.login') }}" style="font-size: 13px; color: var(--color-ink-low);">
                    Customer? <span style="color: var(--color-brand); font-weight: 600;">Sign in to customer portal →</span>
                </a>
            </div>
        </div>
    </div>
</div>
