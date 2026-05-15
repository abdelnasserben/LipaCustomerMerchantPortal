@php use App\Services\FormatService; @endphp
<div>
    {{-- Header --}}
    <div class="sticky top-0 z-10 px-5 lg:px-8 pt-5 lg:pt-8 pb-3" style="background: var(--color-bg);">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-bold lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">{{ __('customer.activity.title') }}</h1>
            <button wire:click="$toggle('showFilters')" class="circle-btn" style="{{ $showFilters ? 'background: var(--color-brand-soft); color: var(--color-brand); border-color: var(--color-brand);' : '' }}">
                <x-icon name="filter" class="w-4 h-4"/>
            </button>
        </div>

        @if($showFilters)
        <div class="flex flex-wrap gap-2 overflow-x-auto pb-2">
            <button wire:click="$set('filterStatus', '')" class="btn btn-sm {{ $filterStatus === '' ? 'btn-primary' : 'btn-secondary' }}">{{ __('customer.activity.all') }}</button>
            <button wire:click="$set('filterStatus', 'COMPLETED')" class="btn btn-sm {{ $filterStatus === 'COMPLETED' ? 'btn-primary' : 'btn-secondary' }}">{{ __('customer.activity.completed') }}</button>
            <button wire:click="$set('filterStatus', 'PENDING')" class="btn btn-sm {{ $filterStatus === 'PENDING' ? 'btn-primary' : 'btn-secondary' }}">{{ __('customer.activity.pending') }}</button>
            <button wire:click="$set('filterStatus', 'DECLINED')" class="btn btn-sm {{ $filterStatus === 'DECLINED' ? 'btn-primary' : 'btn-secondary' }}">{{ __('customer.activity.declined') }}</button>
        </div>
        @endif
    </div>

    <div class="px-5 lg:px-8">
        @if(empty($grouped))
        <div class="empty-state">
            <x-icon name="list" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">{{ __('customer.activity.no_tx_title') }}</div>
            <div style="font-size: 13px;">{{ __('customer.activity.no_tx_sub') }}</div>
        </div>
        @else
        <div class="card overflow-hidden">
            @foreach($grouped as $date => $txs)
            <div class="px-4 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
                <span class="section-title">
                    @php
                        $d = \Carbon\Carbon::parse($date);
                        if ($d->isToday()) echo __('common.today');
                        elseif ($d->isYesterday()) echo __('common.yesterday');
                        else echo $d->translatedFormat('d M Y');
                    @endphp
                </span>
            </div>
            @foreach($txs as $tx)
            <a wire:navigate href="{{ route('customer.transactions.show', $tx['id']) }}" class="tx-row" style="text-decoration: none; color: inherit; border-bottom: 1px solid var(--color-border);">
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
                        {{ FormatService::txTypLabel($tx['type']) }} · {{ FormatService::relativeTime($tx['createdAt']) }}
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
    <div class="h-6"></div>
</div>
