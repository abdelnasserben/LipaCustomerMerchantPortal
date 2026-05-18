@php use App\Services\FormatService; @endphp
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 lg:px-8 pt-5 lg:pt-8 pb-3">
        <div>
            <div class="text-sm" style="color: var(--color-ink-mid);">{{ __('common.good_morning') }}</div>
            <div class="font-bold lg:!text-2xl" style="font-size: 19px; letter-spacing: -0.015em; margin-top: 1px;">{{ $profile['fullName'] }}</div>
        </div>
        <div class="flex gap-2">
            <button class="circle-btn"><x-icon name="search" class="w-4 h-4"/></button>
            <livewire:notifications.bell :actor="'customer'" :key="'bell-customer'"/>
        </div>
    </div>

    {{-- Balance hero --}}
    <div class="mx-5 lg:mx-8 mb-5">
        <div class="balance-hero px-6 py-7 lg:px-10 lg:py-10">
            <div class="grid-bg"></div>
            <div class="relative">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: rgba(255,255,255,0.5);">{{ __('customer.dashboard.available_balance') }}</div>
                        <div class="font-mono font-bold" style="font-size: {{ $balanceHidden ? '28px' : '32px' }}; letter-spacing: -0.02em; line-height: 1;">
                            @if($balanceHidden)
                                <span style="letter-spacing: 0.1em;">••••••</span>
                            @else
                                {{ FormatService::kmf($balance['availableBalance']) }}
                            @endif
                        </div>
                        @if($balance['frozenBalance'] > 0)
                        <div class="text-xs mt-2" style="color: rgba(255,255,255,0.45);">
                            + {{ FormatService::kmf($balance['frozenBalance']) }} {{ __('customer.dashboard.frozen_suffix') }}
                        </div>
                        @endif
                    </div>
                    <button wire:click="toggleBalance"
                        style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.1); border: none; color: rgba(255,255,255,0.7); cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        @if($balanceHidden)<x-icon name="eye" class="w-5 h-5"/>@else<x-icon name="eye-off" class="w-5 h-5"/>@endif
                    </button>
                </div>

                {{-- Quick actions --}}
                <div class="flex gap-3 mt-6">
                    <a wire:navigate href="{{ route('customer.send') }}" style="flex: 1; height: 44px; background: var(--color-brand); border: none; border-radius: 10px; color: #fff; font-weight: 600; font-size: 13.5px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; cursor: pointer;">
                        <x-icon name="send" class="w-4 h-4"/>{{ __('customer.dashboard.send') }}
                    </a>
                    <a wire:navigate href="{{ route('customer.statement') }}" style="flex: 1; height: 44px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; color: rgba(255,255,255,0.85); font-weight: 600; font-size: 13.5px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; cursor: pointer;">
                        <x-icon name="doc" class="w-4 h-4"/>{{ __('customer.dashboard.statement') }}
                    </a>
                    <a wire:navigate href="{{ route('customer.cards') }}" style="flex: 1; height: 44px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; color: rgba(255,255,255,0.85); font-weight: 600; font-size: 13.5px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; cursor: pointer;">
                        <x-icon name="card" class="w-4 h-4"/>{{ __('customer.dashboard.cards') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:px-8">
    {{-- Send again (beneficiaries) --}}
    @if(count($beneficiaries) > 0)
    <div class="px-5 lg:px-0 mb-5 lg:col-span-1 lg:order-2">
        <div class="section-title mb-3">{{ __('customer.dashboard.send_again') }}</div>
        <div class="flex gap-3 overflow-x-auto lg:flex-wrap lg:overflow-visible pb-1" style="-webkit-overflow-scrolling: touch;">
            @foreach($beneficiaries as $b)
            <a wire:navigate href="{{ route('customer.send', ['phone' => $b['phoneNumber'], 'name' => $b['fullName']]) }}"
               class="flex flex-col items-center gap-2 flex-shrink-0 text-decoration-none" style="text-decoration: none;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--color-brand-soft); border: 2px solid var(--color-brand-soft); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: var(--color-brand-deep);">
                    {{ FormatService::initials($b['fullName']) }}
                </div>
                <span style="font-size: 11px; font-weight: 500; color: var(--color-ink-mid); max-width: 52px; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ explode(' ', $b['fullName'])[0] }}
                </span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent activity --}}
    <div class="px-5 lg:px-0 lg:col-span-2 lg:order-1">
        <div class="flex justify-between items-center mb-3">
            <div class="section-title">{{ __('customer.dashboard.recent_activity') }}</div>
            <a wire:navigate href="{{ route('customer.transactions') }}" style="font-size: 12.5px; font-weight: 600; color: var(--color-brand); text-decoration: none;">{{ __('common.view_all') }}</a>
        </div>

        @if(empty($grouped))
        <div class="empty-state">
            <x-icon name="list" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">{{ __('customer.dashboard.no_tx_title') }}</div>
            <div style="font-size: 13px;">{{ __('customer.dashboard.no_tx_sub') }}</div>
        </div>
        @else
        <div class="card overflow-hidden">
            @foreach($grouped as $date => $txs)
            <div class="px-4 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 11px; font-weight: 600; color: var(--color-ink-low); text-transform: uppercase; letter-spacing: 0.06em;">
                    @php
                        $d = \Carbon\Carbon::parse($date);
                        $today = \Carbon\Carbon::today();
                        $yesterday = \Carbon\Carbon::yesterday();
                        if ($d->isToday()) echo __('common.today');
                        elseif ($d->isYesterday()) echo __('common.yesterday');
                        else echo $d->translatedFormat('d M Y');
                    @endphp
                </span>
            </div>
            @foreach($txs as $tx)
            <a wire:navigate href="{{ route('customer.transactions.show', $tx['id']) }}" class="tx-row" style="text-decoration: none; color: inherit; border-bottom: 1px solid var(--color-border);">
                {{-- Icon --}}
                <div style="width: 42px; height: 42px; border-radius: 12px; background: {{ $tx['direction'] === 'in' ? 'var(--color-brand-soft)' : 'var(--color-surface-alt)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    @if($tx['direction'] === 'in')
                        <x-icon name="arrow-down" class="w-5 h-5" style="color: var(--color-brand);"/>
                    @elseif($tx['status'] === 'DECLINED')
                        <x-icon name="x" class="w-5 h-5" style="color: var(--color-danger);"/>
                    @else
                        <x-icon name="arrow-up" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div style="font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $tx['counterparty'] ?? FormatService::txTypLabel($tx['type']) }}
                    </div>
                    <div style="font-size: 12px; color: var(--color-ink-low); margin-top: 2px;">
                        {{ FormatService::relativeTime($tx['createdAt']) }}
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="font-mono font-semibold" style="font-size: 14px; color: {{ $tx['direction'] === 'in' ? 'var(--color-success)' : ($tx['status'] === 'DECLINED' ? 'var(--color-danger)' : 'var(--color-ink-hi)') }};">
                        {{ $tx['direction'] === 'in' ? '+' : '−' }}{{ FormatService::kmf($tx['requestedAmount']) }}
                    </div>
                    <x-tx-status-pill :status="$tx['status']"/>
                </div>
            </a>
            @endforeach
            @endforeach
        </div>
        @endif
    </div>

    </div> {{-- /lg grid --}}

    <div class="h-6"></div>
</div>
