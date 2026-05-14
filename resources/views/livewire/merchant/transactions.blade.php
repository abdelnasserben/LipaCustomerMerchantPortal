@php use App\Services\FormatService; @endphp
<div class="p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-bold" style="font-size: 24px; letter-spacing: -0.02em;">Transactions</h1>
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">All wallet activity</div>
        </div>
        <div class="flex gap-2">
            <button wire:click="$toggle('showFilters')" class="btn btn-secondary btn-md {{ $showFilters ? 'ring-2 ring-brand' : '' }}">
                <x-icon name="filter" class="w-4 h-4"/>Filters
            </button>
            <button class="btn btn-secondary btn-md">
                <x-icon name="download" class="w-4 h-4"/>Export
            </button>
        </div>
    </div>

    @if($showFilters)
    <div class="card p-4 mb-5">
        <div class="flex flex-wrap gap-3">
            <div>
                <label class="label">Status</label>
                <select wire:model.live="filterStatus" class="input" style="height: 40px; font-size: 13px; width: auto;">
                    <option value="">All statuses</option>
                    <option value="COMPLETED">Completed</option>
                    <option value="PENDING">Pending</option>
                    <option value="DECLINED">Declined</option>
                </select>
            </div>
            <div>
                <label class="label">Type</label>
                <select wire:model.live="filterType" class="input" style="height: 40px; font-size: 13px; width: auto;">
                    <option value="">All types</option>
                    <option value="CARD_SALE">Card Sale</option>
                    <option value="MERCHANT_TO_MERCHANT">M2M Transfer</option>
                    <option value="COMMISSION_PAYOUT">Commission</option>
                </select>
            </div>
        </div>
    </div>
    @endif

    <div class="card overflow-hidden">
        <div class="hidden md:grid grid-cols-12 px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="col-span-3 section-title">Counterparty</div>
            <div class="col-span-2 section-title">Operator</div>
            <div class="col-span-2 section-title">Terminal</div>
            <div class="col-span-2 section-title">Status</div>
            <div class="col-span-1 section-title">Time</div>
            <div class="col-span-2 section-title text-right">Amount</div>
        </div>
        @forelse($transactions as $tx)
        <a wire:navigate href="{{ route('merchant.transactions.show', $tx['id']) }}"
           class="grid grid-cols-1 md:grid-cols-12 items-center px-5 py-3 hover:bg-[var(--color-surface-alt)] transition-colors"
           style="border-bottom: 1px solid var(--color-border); text-decoration: none; color: inherit;">
            <div class="col-span-3 flex items-center gap-3 mb-2 md:mb-0">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $tx['direction'] === 'in' ? 'var(--color-brand-soft)' : 'var(--color-surface-alt)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    @if($tx['direction'] === 'in')
                        <x-icon name="arrow-down" class="w-4 h-4" style="color: var(--color-brand);"/>
                    @elseif($tx['status'] === 'DECLINED')
                        <x-icon name="x" class="w-4 h-4" style="color: var(--color-danger);"/>
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
            <div class="col-span-1 hidden md:block" style="font-size: 11px; color: var(--color-ink-low);">{{ FormatService::dateTime($tx['createdAt'], 'H:i') }}</div>
            <div class="col-span-2 text-right">
                <div class="font-mono font-semibold" style="font-size: 14px; color: {{ $tx['direction'] === 'in' && $tx['status'] === 'COMPLETED' ? 'var(--color-success)' : ($tx['status'] === 'DECLINED' ? 'var(--color-danger)' : 'var(--color-ink-hi)') }};">
                    {{ $tx['direction'] === 'in' ? '+' : '−' }}{{ FormatService::kmf(abs($tx['requestedAmount'])) }}
                </div>
                <div class="md:hidden mt-1"><x-tx-status-pill :status="$tx['status']"/></div>
            </div>
        </a>
        @empty
        <div class="empty-state">
            <x-icon name="list" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600;">No transactions found</div>
        </div>
        @endforelse
    </div>
</div>
