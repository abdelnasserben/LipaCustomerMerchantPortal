@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8 lg:max-w-3xl">
    <h1 class="font-bold mb-5 lg:mb-6 lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">{{ __('merchant.profile.title') }}</h1>

    {{-- Identity card --}}
    <div class="card p-5 lg:p-6 mb-5">
        <div class="flex items-start gap-4">
            <div style="width: 56px; height: 56px; border-radius: 16px; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; color: var(--color-brand-deep); flex-shrink: 0;">
                {{ FormatService::initials($profile['businessName']) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-bold truncate" style="font-size: 18px;">{{ $profile['businessName'] }}</div>
                <div class="truncate" style="font-size: 14px; color: var(--color-ink-mid); margin-top: 2px;">{{ $profile['legalName'] }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <x-status-pill :status="$profile['status']" size="lg"/>
                    <x-status-pill :status="$profile['kycLevel']" size="lg"/>
                </div>
            </div>
        </div>
    </div>

    {{-- Balance --}}
    <div class="card-flat p-5 mb-5">
        <div class="flex justify-between items-center">
            <div>
                <div class="section-title mb-1">{{ __('merchant.profile.wallet_balance') }}</div>
                <div class="font-mono font-bold" style="font-size: 24px; letter-spacing: -0.015em;">{{ FormatService::kmf($balance['availableBalance']) }}</div>
            </div>
            <div class="text-right">
                <x-status-pill :status="$balance['walletStatus']" size="lg"/>
                @if($balance['frozenBalance'] > 0)
                <div class="mt-1" style="font-size: 12px; color: var(--color-ink-low);">{{ FormatService::kmf($balance['frozenBalance']) }} {{ __('merchant.profile.frozen_suffix') }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Business details --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="section-title">{{ __('merchant.profile.business_details') }}</div>
        </div>
        @php
        $rows = [
            [__('merchant.profile.merchant_id'), $profile['externalRef']],
            [__('merchant.profile.business_type'), $profile['businessType']],
            [__('merchant.profile.category'), $profile['category']],
            [__('merchant.profile.tax_id'), $profile['taxId']],
            [__('merchant.profile.phone'), '+' . $profile['phoneCountryCode'] . ' ' . $profile['phoneNumber']],
            [__('merchant.profile.island'), $profile['addressIsland'] ?? '—'],
            [__('merchant.profile.city'), $profile['addressCity'] ?? '—'],
            [__('merchant.profile.district'), $profile['addressDistrict'] ?? '—'],
            [__('merchant.profile.member_since'), FormatService::date($profile['createdAt'])],
        ];
        @endphp
        @foreach($rows as $row)
        <div class="flex justify-between items-center gap-3 px-4 md:px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <span class="flex-shrink-0" style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
            <span class="truncate text-right" style="font-size: 13px; font-weight: 500;">{{ $row[1] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Capabilities --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="section-title">{{ __('merchant.profile.capabilities') }}</div>
        </div>
        <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('merchant.profile.cash_out_enabled') }}</span>
            <span class="pill {{ $profile['canCashOut'] ? 'pill-success' : 'pill-declined' }}">{{ $profile['canCashOut'] ? __('common.yes') : __('common.no') }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3">
            <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('merchant.profile.m2m_transfers') }}</span>
            <span class="pill {{ $profile['canReceiveFromMerchant'] ? 'pill-success' : 'pill-declined' }}">{{ $profile['canReceiveFromMerchant'] ? __('common.enabled') : __('common.disabled') }}</span>
        </div>
    </div>

    {{-- Links --}}
    <div class="card overflow-hidden mb-5">
        <a wire:navigate href="{{ route('merchant.security') }}" class="flex items-center justify-between px-5 py-3" style="text-decoration: none; color: inherit;">
            <div class="flex items-center gap-3">
                <x-icon name="shield" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                <span style="font-size: 14px;">{{ __('merchant.profile.security_pin') }}</span>
            </div>
            <x-icon name="chev-right" class="w-4 h-4" style="color: var(--color-ink-low);"/>
        </a>
    </div>

    <form method="POST" action="{{ route('merchant.logout') }}">
        @csrf
        <button type="submit" class="btn btn-secondary btn-lg">
            <x-icon name="arrow-left" class="w-4 h-4"/>
            {{ __('common.sign_out') }}
        </button>
    </form>
</div>
