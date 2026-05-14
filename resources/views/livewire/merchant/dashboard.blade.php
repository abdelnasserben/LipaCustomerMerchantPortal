@php use App\Services\FormatService; @endphp
<div class="p-6 lg:p-8">
    {{-- Top header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="text-sm" style="color: var(--color-ink-mid);">Good morning</div>
            <h1 class="font-bold" style="font-size: 24px; letter-spacing: -0.02em; margin-top: 2px;">{{ $profile['businessName'] }}</h1>
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">{{ $profile['externalRef'] }} · {{ $profile['addressCity'] }}, {{ $profile['addressDistrict'] }}</div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('merchant.send') }}" class="btn btn-primary btn-md">
                <x-icon name="send" class="w-4 h-4"/>Send M2M
            </a>
            <button class="circle-btn">
                <x-icon name="refresh" class="w-4 h-4"/>
            </button>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card col-span-2">
            <div class="section-title mb-2">Available Balance</div>
            <div class="font-mono font-bold" style="font-size: 28px; letter-spacing: -0.02em; color: var(--color-ink-hi);">{{ FormatService::kmf($balance['availableBalance']) }}</div>
            @if($balance['frozenBalance'] > 0)
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 4px;">+ {{ FormatService::kmf($balance['frozenBalance']) }} frozen</div>
            @endif
            <div class="flex gap-4 mt-4 pt-4" style="border-top: 1px solid var(--color-border);">
                <div>
                    <div class="section-title mb-1">Today</div>
                    <div class="font-mono font-semibold" style="font-size: 15px; color: var(--color-success);">+{{ FormatService::kmf($balance['todayIn']) }}</div>
                </div>
                <div>
                    <div class="section-title mb-1">Sales</div>
                    <div class="font-mono font-semibold" style="font-size: 15px;">{{ $balance['todayCount'] }}</div>
                </div>
                <div>
                    <div class="section-title mb-1">This week</div>
                    <div class="font-mono font-semibold" style="font-size: 15px; color: var(--color-success);">+{{ FormatService::kmf($balance['weekIn']) }}</div>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="section-title mb-3">Terminals Online</div>
            <div class="font-mono font-bold" style="font-size: 32px; color: var(--color-ink-hi);">{{ $activeTerminals }}</div>
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 4px;">of {{ count($terminals) }} total</div>
            <a href="{{ route('merchant.terminals') }}" class="mt-3 inline-flex items-center gap-1" style="font-size: 12.5px; color: var(--color-brand); font-weight: 600; text-decoration: none;">View terminals <x-icon name="chev-right" class="w-3 h-3"/></a>
        </div>
        <div class="stat-card">
            <div class="section-title mb-3">Wallet Status</div>
            <x-status-pill :status="$balance['walletStatus']" size="lg"/>
            <div style="font-size: 12px; color: var(--color-ink-low); margin-top: 8px;">Last update: {{ FormatService::relativeTime($balance['updatedAt']) }}</div>
        </div>
    </div>

    {{-- Main grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sales chart --}}
        <div class="lg:col-span-2">
            <div class="card p-5">
                <div class="flex justify-between items-center mb-5">
                    <div class="font-semibold" style="font-size: 15px;">Sales Today (KMF)</div>
                    <span class="pill pill-success">Live</span>
                </div>
                {{-- Bar chart --}}
                <div class="flex items-end gap-2" style="height: 120px;">
                    @php $maxAmt = max(array_column($chart, 'amount')); @endphp
                    @foreach($chart as $bar)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full rounded-t" style="height: {{ max(4, ($bar['amount'] / $maxAmt) * 100) }}px; background: var(--color-brand); opacity: 0.85; min-width: 20px;"></div>
                        <div style="font-size: 10px; color: var(--color-ink-low);">{{ $bar['hour'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Terminal status --}}
        <div class="card p-5">
            <div class="font-semibold mb-4" style="font-size: 15px;">Terminal Status</div>
            <div class="flex flex-col gap-3">
                @foreach($terminals as $t)
                <div class="flex items-center justify-between">
                    <div>
                        <div style="font-size: 13px; font-weight: 500;">{{ $t['operatorAlias'] }}</div>
                        <div class="font-mono" style="font-size: 11px; color: var(--color-ink-low);">{{ $t['serialNumber'] }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div style="width: 7px; height: 7px; border-radius: 50%; background: {{ $t['status'] === 'ACTIVE' ? 'var(--color-brand)' : 'var(--color-ink-low)' }};"></div>
                        <span style="font-size: 12px; color: var(--color-ink-mid);">{{ FormatService::relativeTime($t['lastSeenAt']) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent transactions --}}
    <div class="mt-6">
        <div class="flex justify-between items-center mb-4">
            <div class="font-semibold" style="font-size: 15px;">Recent Payments</div>
            <a href="{{ route('merchant.transactions') }}" style="font-size: 12.5px; color: var(--color-brand); font-weight: 600; text-decoration: none;">View all →</a>
        </div>
        <div class="card overflow-hidden">
            {{-- Table header --}}
            <div class="hidden md:grid grid-cols-12 px-4 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
                <div class="col-span-3 section-title">Counterparty</div>
                <div class="col-span-2 section-title">Operator</div>
                <div class="col-span-2 section-title">Terminal</div>
                <div class="col-span-2 section-title">Status</div>
                <div class="col-span-3 section-title text-right">Amount</div>
            </div>
            @forelse($transactions as $tx)
            <a href="{{ route('merchant.transactions.show', $tx['id']) }}" class="grid grid-cols-1 md:grid-cols-12 items-center px-4 py-3 hover:bg-[var(--color-surface-alt)] transition-colors" style="border-bottom: 1px solid var(--color-border); text-decoration: none; color: inherit;">
                <div class="col-span-3 flex items-center gap-3 mb-2 md:mb-0">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: {{ $tx['direction'] === 'in' ? 'var(--color-brand-soft)' : 'var(--color-surface-alt)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if($tx['direction'] === 'in')
                            <x-icon name="arrow-down" class="w-4 h-4" style="color: var(--color-brand);"/>
                        @else
                            <x-icon name="arrow-up" class="w-4 h-4" style="color: var(--color-ink-mid);"/>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div style="font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $tx['counterparty'] }}</div>
                        <div class="font-mono" style="font-size: 11px; color: var(--color-ink-low);">{{ FormatService::txTypLabel($tx['type']) }}</div>
                    </div>
                </div>
                <div class="col-span-2 hidden md:block" style="font-size: 13px; color: var(--color-ink-mid);">{{ $tx['operatorName'] }}</div>
                <div class="col-span-2 hidden md:block font-mono" style="font-size: 12px; color: var(--color-ink-low);">{{ $tx['terminalSerial'] }}</div>
                <div class="col-span-2 hidden md:block"><x-tx-status-pill :status="$tx['status']"/></div>
                <div class="col-span-3 text-right">
                    <div class="font-mono font-semibold" style="font-size: 14px; color: {{ $tx['direction'] === 'in' && $tx['status'] === 'COMPLETED' ? 'var(--color-success)' : ($tx['status'] === 'DECLINED' ? 'var(--color-danger)' : 'var(--color-ink-hi)') }};">
                        {{ $tx['direction'] === 'in' ? '+' : '−' }}{{ FormatService::kmf(abs($tx['requestedAmount'])) }}
                    </div>
                    <div class="md:hidden mt-1"><x-tx-status-pill :status="$tx['status']"/></div>
                </div>
            </a>
            @empty
            <div class="empty-state">
                <x-icon name="list" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
                <div style="font-size: 15px; font-weight: 600;">No transactions yet</div>
            </div>
            @endforelse
        </div>
    </div>
</div>
