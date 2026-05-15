@php use App\Services\FormatService; @endphp
<div>
    <div class="px-5 lg:px-8 pt-5 lg:pt-8">
        <h1 class="font-bold mb-5 lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">Profile</h1>

        <div class="lg:grid lg:grid-cols-2 lg:gap-5">

        {{-- Identity card --}}
        <div class="card p-5 mb-5">
            <div class="flex items-center gap-4">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; color: var(--color-brand-deep);">
                    {{ FormatService::initials($profile['fullName']) }}
                </div>
                <div class="flex-1">
                    <div class="font-bold" style="font-size: 17px;">{{ $profile['fullName'] }}</div>
                    <div class="font-mono text-sm" style="color: var(--color-ink-mid); margin-top: 2px;">
                        +{{ $profile['phoneCountryCode'] }} {{ $profile['phoneNumber'] }}
                    </div>
                </div>
                <div>
                    <x-status-pill :status="$profile['kycLevel']"/>
                </div>
            </div>
        </div>

        {{-- Balance summary --}}
        <div class="card-flat p-4 mb-5">
            <div class="flex justify-between items-center">
                <div>
                    <div class="section-title mb-1">Wallet Balance</div>
                    <div class="font-mono font-bold" style="font-size: 22px; letter-spacing: -0.015em;">{{ FormatService::kmf($balance['availableBalance']) }}</div>
                </div>
                <x-status-pill :status="$balance['walletStatus']"/>
            </div>
            @if($balance['frozenBalance'] > 0)
            <div class="mt-3 text-sm" style="color: var(--color-ink-mid);">
                {{ FormatService::kmf($balance['frozenBalance']) }} frozen
            </div>
            @endif
        </div>

        {{-- Account details --}}
        <div class="card overflow-hidden mb-5">
            <div class="px-4 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
                <div class="section-title">Account Details</div>
            </div>
            @php
            $rows = [
                ['Customer ID', $profile['externalRef']],
                ['Status', null, $profile['status']],
                ['KYC Level', null, $profile['kycLevel']],
                ['Island', $profile['addressIsland'] ?? '—'],
                ['City', $profile['addressCity'] ?? '—'],
            ];
            if (isset($profile['kycVerifiedAt'])) {
                $rows[] = ['KYC Verified', FormatService::date($profile['kycVerifiedAt'])];
            }
            $rows[] = ['Member since', FormatService::date($profile['createdAt'])];
            @endphp
            @foreach($rows as $row)
            <div class="flex justify-between items-center px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
                @if(isset($row[2]))
                    <x-status-pill :status="$row[2]"/>
                @else
                    <span class="font-mono" style="font-size: 13px;">{{ $row[1] }}</span>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Limits --}}
        <div class="card overflow-hidden mb-5">
            <div class="px-4 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
                <div class="flex justify-between items-center">
                    <div class="section-title">Transaction Limits</div>
                    <span style="font-size: 12px; color: var(--color-ink-low);">{{ $limits['profileName'] }}</span>
                </div>
            </div>
            @php
            $limitRows = [
                ['Per transaction', $limits['maxTransactionAmount']],
                ['Daily limit', $limits['maxDailyAmount']],
                ['Monthly limit', $limits['maxMonthlyAmount']],
                ['Daily transactions', null, $limits['maxDailyTransactionCount'] . ' max'],
            ];
            @endphp
            @foreach($limitRows as $row)
            <div class="flex justify-between items-center px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
                <span class="font-mono font-semibold" style="font-size: 13px;">
                    @if(isset($row[2])){{ $row[2] }}
                    @else{{ FormatService::kmf($row[1]) }}
                    @endif
                </span>
            </div>
            @endforeach
        </div>

        {{-- Links --}}
        <div class="card overflow-hidden mb-5">
            <a wire:navigate href="{{ route('customer.statement') }}" class="flex items-center justify-between px-4 py-3 text-decoration-none" style="border-bottom: 1px solid var(--color-border); text-decoration: none; color: inherit;">
                <div class="flex items-center gap-3">
                    <x-icon name="doc" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                    <span style="font-size: 14px;">Account Statement</span>
                </div>
                <x-icon name="chev-right" class="w-4 h-4" style="color: var(--color-ink-low);"/>
            </a>
            <a wire:navigate href="{{ route('customer.security') }}" class="flex items-center justify-between px-4 py-3" style="text-decoration: none; color: inherit;">
                <div class="flex items-center gap-3">
                    <x-icon name="shield" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                    <span style="font-size: 14px;">Security & PIN</span>
                </div>
                <x-icon name="chev-right" class="w-4 h-4" style="color: var(--color-ink-low);"/>
            </a>
        </div>

        </div> {{-- /lg grid --}}

        <a href="{{ route('customer.login') }}" class="btn btn-secondary btn-lg btn-full">
            <x-icon name="arrow-left" class="w-4 h-4"/>
            Sign Out
        </a>
    </div>
    <div class="h-6"></div>
</div>
