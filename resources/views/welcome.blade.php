<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('welcome.title') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/favicon-192x192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="background: var(--color-bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;">
    <div style="width: 100%; max-width: 460px; text-align: center;">
        {{-- Logo --}}
        <div class="flex justify-center mb-8">
            <div class="flex items-center gap-4">
                <x-lipa-mark :size="56"/>
                <div class="text-left">
                    <div style="font-size: 28px; font-weight: 800; letter-spacing: -0.025em; color: var(--color-ink-hi);">{{ __('common.brand') }}</div>
                    <div style="font-size: 11px; font-weight: 600; color: var(--color-ink-low); letter-spacing: 0.08em; text-transform: uppercase;">Portal</div>
                </div>
            </div>
        </div>

        <h1 style="font-size: 22px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: var(--color-ink-hi);">{{ __('welcome.choose_portal') }}</h1>
        <p style="font-size: 14px; color: var(--color-ink-mid); margin-bottom: 36px; line-height: 1.5;">{{ __('welcome.access_account') }}</p>

        <div class="flex flex-col gap-4">
            {{-- Customer card --}}
            <a wire:navigate href="{{ route('customer.login') }}" style="text-decoration: none;">
                <div class="card p-6 text-left cursor-pointer" style="border: 1.5px solid var(--color-border); transition: box-shadow .15s;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div style="width: 52px; height: 52px; border-radius: 14px; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center;">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="8" r="4" stroke="currentColor" style="color: var(--color-brand-deep);" stroke-width="1.8"/>
                                    <path d="M4 21c0-4 4-7 8-7s8 3 8 7" stroke="currentColor" style="color: var(--color-brand-deep);" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 16px; font-weight: 700; color: var(--color-ink-hi);">{{ __('welcome.customer') }}</div>
                                <div style="font-size: 13px; color: var(--color-ink-mid); margin-top: 2px;">{{ __('welcome.customer_desc') }}</div>
                            </div>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="color: var(--color-ink-low); flex-shrink: 0;">
                            <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <span class="pill pill-neutral">{{ __('welcome.mobile_first') }}</span>
                        <span class="pill pill-neutral">{{ __('welcome.sessions_15') }}</span>
                    </div>
                </div>
            </a>

            {{-- Merchant card --}}
            <a wire:navigate href="{{ route('merchant.login') }}" style="text-decoration: none;">
                <div class="card p-6 text-left cursor-pointer" style="border: 1.5px solid var(--color-border); transition: box-shadow .15s;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div style="width: 52px; height: 52px; border-radius: 14px; background: #0a0a0a; display: flex; align-items: center; justify-content: center;">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                    <rect x="2" y="7" width="20" height="14" rx="2" stroke="white" stroke-width="1.8"/>
                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M12 12v3M10 14h4" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 16px; font-weight: 700; color: var(--color-ink-hi);">{{ __('welcome.merchant') }}</div>
                                <div style="font-size: 13px; color: var(--color-ink-mid); margin-top: 2px;">{{ __('welcome.merchant_desc') }}</div>
                            </div>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="color: var(--color-ink-low); flex-shrink: 0;">
                            <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <span class="pill pill-neutral">{{ __('welcome.dashboard_first') }}</span>
                        <span class="pill pill-neutral">{{ __('welcome.sessions_8h') }}</span>
                    </div>
                </div>
            </a>
        </div>

        <div style="font-size: 12px; color: var(--color-ink-low); margin-top: 32px; line-height: 1.6;">
            {{ __('welcome.footer') }}
        </div>
    </div>
    @livewireScripts
</body>
</html>
