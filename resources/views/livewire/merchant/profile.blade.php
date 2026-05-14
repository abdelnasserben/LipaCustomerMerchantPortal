@php use App\Services\FormatService; @endphp
<div class="p-6 lg:p-8 max-w-3xl">
    <h1 class="font-bold mb-6" style="font-size: 22px; letter-spacing: -0.02em;">Business Profile</h1>

    {{-- Identity card --}}
    <div class="card p-6 mb-5">
        <div class="flex items-start gap-4">
            <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; color: var(--color-brand-deep); flex-shrink: 0;">
                {{ FormatService::initials($profile['businessName']) }}
            </div>
            <div class="flex-1">
                <div class="font-bold" style="font-size: 20px;">{{ $profile['businessName'] }}</div>
                <div style="font-size: 14px; color: var(--color-ink-mid); margin-top: 2px;">{{ $profile['legalName'] }}</div>
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
                <div class="section-title mb-1">Wallet Balance</div>
                <div class="font-mono font-bold" style="font-size: 24px; letter-spacing: -0.015em;">{{ FormatService::kmf($balance['availableBalance']) }}</div>
            </div>
            <div class="text-right">
                <x-status-pill :status="$balance['walletStatus']" size="lg"/>
                @if($balance['frozenBalance'] > 0)
                <div class="mt-1" style="font-size: 12px; color: var(--color-ink-low);">{{ FormatService::kmf($balance['frozenBalance']) }} frozen</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Business details --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="section-title">Business Details</div>
        </div>
        @php
        $rows = [
            ['Merchant ID', $profile['externalRef']],
            ['Business Type', $profile['businessType']],
            ['Category', $profile['category']],
            ['Tax ID', $profile['taxId']],
            ['Phone', '+' . $profile['phoneCountryCode'] . ' ' . $profile['phoneNumber']],
            ['Island', $profile['addressIsland'] ?? '—'],
            ['City', $profile['addressCity'] ?? '—'],
            ['District', $profile['addressDistrict'] ?? '—'],
            ['Member since', FormatService::date($profile['createdAt'])],
        ];
        @endphp
        @foreach($rows as $row)
        <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <span style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
            <span style="font-size: 13px; font-weight: 500;">{{ $row[1] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Capabilities --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="section-title">Account Capabilities</div>
        </div>
        <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <span style="font-size: 13px; color: var(--color-ink-mid);">Cash-out enabled</span>
            <span class="pill {{ $profile['canCashOut'] ? 'pill-success' : 'pill-declined' }}">{{ $profile['canCashOut'] ? 'Yes' : 'No' }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3">
            <span style="font-size: 13px; color: var(--color-ink-mid);">M2M transfers</span>
            <span class="pill {{ $profile['canReceiveFromMerchant'] ? 'pill-success' : 'pill-declined' }}">{{ $profile['canReceiveFromMerchant'] ? 'Enabled' : 'Disabled' }}</span>
        </div>
    </div>

    {{-- Links --}}
    <div class="card overflow-hidden mb-5">
        <a href="{{ route('merchant.security') }}" class="flex items-center justify-between px-5 py-3" style="text-decoration: none; color: inherit;">
            <div class="flex items-center gap-3">
                <x-icon name="shield" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                <span style="font-size: 14px;">Security & PIN</span>
            </div>
            <x-icon name="chev-right" class="w-4 h-4" style="color: var(--color-ink-low);"/>
        </a>
    </div>

    <a href="{{ route('merchant.login') }}" class="btn btn-secondary btn-lg">
        <x-icon name="arrow-left" class="w-4 h-4"/>
        Sign Out (clear local session)
    </a>
    <div class="mt-3 text-sm" style="color: var(--color-ink-low);">Note: Merchant sessions last 8 hours. Sign-out only clears local storage — the JWT remains valid until natural expiry.</div>
</div>
