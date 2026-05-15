@php use App\Services\FormatService; @endphp
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 lg:px-8 pt-5 lg:pt-8 pb-3">
        <div class="min-w-0">
            <div class="text-sm" style="color: var(--color-ink-mid);">Good morning</div>
            <div class="font-bold lg:!text-2xl truncate" style="font-size: 19px; letter-spacing: -0.015em; margin-top: 1px;">{{ $profile['businessName'] }}</div>
            <div class="hidden lg:block" style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">{{ $profile['externalRef'] }} · {{ $profile['addressCity'] }}, {{ $profile['addressDistrict'] }}</div>
        </div>
        <div class="flex gap-2">
            <button class="circle-btn"><x-icon name="refresh" class="w-4 h-4"/></button>
            <button class="circle-btn"><x-icon name="bell" class="w-5 h-5"/></button>
        </div>
    </div>

    {{-- Balance hero --}}
    <div class="mx-5 lg:mx-8 mb-5">
        <div class="balance-hero px-6 py-7 lg:px-10 lg:py-10">
            <div class="grid-bg"></div>
            <div class="relative">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: rgba(255,255,255,0.5);">Available Balance</div>
                        <div class="font-mono font-bold" style="font-size: 32px; letter-spacing: -0.02em; line-height: 1;">
                            {{ FormatService::kmf($balance['availableBalance']) }}
                        </div>
                        @if($balance['frozenBalance'] > 0)
                        <div class="text-xs mt-2" style="color: rgba(255,255,255,0.45);">
                            + {{ FormatService::kmf($balance['frozenBalance']) }} frozen
                        </div>
                        @endif
                    </div>
                    <x-status-pill :status="$balance['walletStatus']" size="lg"/>
                </div>

                {{-- Today's quick stats --}}
                <div class="grid grid-cols-3 gap-3 mt-6 pt-5" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <div>
                        <div class="text-xs uppercase tracking-wider mb-1" style="color: rgba(255,255,255,0.45); font-weight: 600;">Today</div>
                        <div class="font-mono font-semibold" style="font-size: 14px; color: #4ade80;">+{{ FormatService::kmf($balance['todayIn']) }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider mb-1" style="color: rgba(255,255,255,0.45); font-weight: 600;">Sales</div>
                        <div class="font-mono font-semibold" style="font-size: 14px; color: #fff;">{{ $balance['todayCount'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider mb-1" style="color: rgba(255,255,255,0.45); font-weight: 600;">Week</div>
                        <div class="font-mono font-semibold" style="font-size: 14px; color: #4ade80;">+{{ FormatService::kmf($balance['weekIn']) }}</div>
                    </div>
                </div>

                {{-- Quick actions --}}
                <div class="flex gap-3 mt-6">
                    <a wire:navigate href="{{ route('merchant.send') }}" style="flex: 1; height: 44px; background: #0c7a3e; border: none; border-radius: 10px; color: #fff; font-weight: 600; font-size: 13.5px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; cursor: pointer;">
                        <x-icon name="send" class="w-4 h-4"/>Send M2M
                    </a>
                    <a wire:navigate href="{{ route('merchant.statement') }}" style="flex: 1; height: 44px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; color: rgba(255,255,255,0.85); font-weight: 600; font-size: 13.5px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; cursor: pointer;">
                        <x-icon name="doc" class="w-4 h-4"/>Statement
                    </a>
                    <a wire:navigate href="{{ route('merchant.terminals') }}" style="flex: 1; height: 44px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; color: rgba(255,255,255,0.85); font-weight: 600; font-size: 13.5px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; cursor: pointer;">
                        <x-icon name="device" class="w-4 h-4"/>Terminals
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:px-8">

    {{-- Sales today chart --}}
    <div class="px-5 lg:px-0 mb-5 lg:col-span-2 lg:order-1">
        <div class="card p-5">
            <div class="flex justify-between items-center mb-4">
                <div class="section-title">Sales Today (KMF)</div>
                <span class="pill pill-success">Live</span>
            </div>
            <div class="flex items-end gap-2" style="height: 120px;">
                @php $maxAmt = max(array_column($chart, 'amount')) ?: 1; @endphp
                @foreach($chart as $bar)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full rounded-t" style="height: {{ max(4, ($bar['amount'] / $maxAmt) * 100) }}px; background: var(--color-brand); opacity: 0.85; min-width: 14px;"></div>
                    <div style="font-size: 10px; color: var(--color-ink-low);">{{ $bar['hour'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Terminal status --}}
    <div class="px-5 lg:px-0 mb-5 lg:col-span-1 lg:order-2">
        <div class="card p-5">
            <div class="flex justify-between items-center mb-4">
                <div class="section-title">Terminals</div>
                <a wire:navigate href="{{ route('merchant.terminals') }}" style="font-size: 12.5px; font-weight: 600; color: var(--color-brand); text-decoration: none;">View all</a>
            </div>
            <div class="font-mono font-bold mb-1" style="font-size: 28px; color: var(--color-ink-hi); line-height: 1;">{{ $activeTerminals }}<span style="font-size: 14px; color: var(--color-ink-low); font-weight: 500;"> / {{ count($terminals) }}</span></div>
            <div style="font-size: 12px; color: var(--color-ink-low); margin-bottom: 14px;">online</div>
            <div class="flex flex-col gap-3">
                @foreach($terminals as $t)
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <div class="truncate" style="font-size: 13px; font-weight: 500;">{{ $t['operatorAlias'] }}</div>
                        <div class="font-mono" style="font-size: 11px; color: var(--color-ink-low);">{{ $t['serialNumber'] }}</div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div style="width: 7px; height: 7px; border-radius: 50%; background: {{ $t['status'] === 'ACTIVE' ? 'var(--color-brand)' : 'var(--color-ink-low)' }};"></div>
                        <span style="font-size: 11px; color: var(--color-ink-mid);">{{ FormatService::relativeTime($t['lastSeenAt']) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent payments --}}
    <div class="px-5 lg:px-0 lg:col-span-3 lg:order-3">
        <div class="flex justify-between items-center mb-3">
            <div class="section-title">Recent Payments</div>
            <a wire:navigate href="{{ route('merchant.transactions') }}" style="font-size: 12.5px; font-weight: 600; color: var(--color-brand); text-decoration: none;">View all</a>
        </div>

        @if(empty($transactions))
        <div class="empty-state">
            <x-icon name="list" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600;">No transactions yet</div>
        </div>
        @else
        <div class="card overflow-hidden">
            {{-- Desktop table header --}}
            <div class="hidden lg:grid grid-cols-12 px-4 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
                <div class="col-span-4 section-title">Counterparty</div>
                <div class="col-span-2 section-title">Operator</div>
                <div class="col-span-2 section-title">Terminal</div>
                <div class="col-span-1 section-title">Status</div>
                <div class="col-span-3 section-title text-right">Amount</div>
            </div>
            @foreach($transactions as $tx)
            <a wire:navigate href="{{ route('merchant.transactions.show', $tx['id']) }}" class="flex lg:grid lg:grid-cols-12 items-center gap-3 px-4 py-3 hover:bg-[var(--color-surface-alt)] transition-colors" style="border-bottom: 1px solid var(--color-border); text-decoration: none; color: inherit;">
                <div class="lg:col-span-4 flex items-center gap-3 flex-1 min-w-0">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: {{ $tx['direction'] === 'in' ? 'var(--color-brand-soft)' : 'var(--color-surface-alt)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if($tx['direction'] === 'in')
                            <x-icon name="arrow-down" class="w-5 h-5" style="color: var(--color-brand);"/>
                        @else
                            <x-icon name="arrow-up" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate" style="font-size: 14px; font-weight: 500;">{{ $tx['counterparty'] }}</div>
                        <div style="font-size: 12px; color: var(--color-ink-low); margin-top: 2px;">{{ FormatService::txTypLabel($tx['type']) }} · {{ FormatService::relativeTime($tx['createdAt'] ?? null) }}</div>
                    </div>
                </div>
                <div class="hidden lg:block lg:col-span-2" style="font-size: 13px; color: var(--color-ink-mid);">{{ $tx['operatorName'] }}</div>
                <div class="hidden lg:block lg:col-span-2 font-mono" style="font-size: 12px; color: var(--color-ink-low);">{{ $tx['terminalSerial'] }}</div>
                <div class="hidden lg:block lg:col-span-1"><x-tx-status-pill :status="$tx['status']"/></div>
                <div class="lg:col-span-3 text-right flex-shrink-0">
                    <div class="font-mono font-semibold" style="font-size: 14px; color: {{ $tx['direction'] === 'in' && $tx['status'] === 'COMPLETED' ? 'var(--color-success)' : ($tx['status'] === 'DECLINED' ? 'var(--color-danger)' : 'var(--color-ink-hi)') }};">
                        {{ $tx['direction'] === 'in' ? '+' : '−' }}{{ FormatService::kmf(abs($tx['requestedAmount'])) }}
                    </div>
                    <div class="lg:hidden mt-1"><x-tx-status-pill :status="$tx['status']"/></div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    </div> {{-- /lg grid --}}

    <div class="h-6"></div>
</div>
