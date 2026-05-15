@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8">
    {{-- Back --}}
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('customer.transactions') }}" class="circle-btn">
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </a>
        <h1 class="font-bold lg:!text-2xl" style="font-size: 19px; letter-spacing: -0.02em;">{{ __('customer.transaction.detail_title') }}</h1>
    </div>

    @if(!$tx)
    <div class="empty-state">
        <x-icon name="warn" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
        <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">{{ __('customer.transaction.not_found') }}</div>
        <a wire:navigate href="{{ route('customer.transactions') }}" class="btn btn-secondary btn-md mt-2">{{ __('customer.transaction.back_to_activity') }}</a>
    </div>
    @else

    {{-- Amount hero --}}
    <div class="card p-6 text-center mb-5">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: {{ $tx['direction'] === 'in' ? 'var(--color-brand-soft)' : ($tx['status'] === 'DECLINED' ? 'var(--color-danger-soft)' : 'var(--color-surface-alt)') }}; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            @if($tx['direction'] === 'in')
                <x-icon name="arrow-down" class="w-6 h-6" style="color: var(--color-brand);"/>
            @elseif($tx['status'] === 'DECLINED')
                <x-icon name="x" class="w-6 h-6" style="color: var(--color-danger);"/>
            @else
                <x-icon name="arrow-up" class="w-6 h-6" style="color: var(--color-ink-mid);"/>
            @endif
        </div>
        <div class="font-mono font-bold" style="font-size: 30px; letter-spacing: -0.02em; color: {{ $tx['direction'] === 'in' ? 'var(--color-success)' : ($tx['status'] === 'DECLINED' ? 'var(--color-danger)' : 'var(--color-ink-hi)') }};">
            {{ $tx['direction'] === 'in' ? '+' : '−' }}{{ FormatService::kmf($tx['requestedAmount']) }}
        </div>
        <div class="mt-3">
            <x-tx-status-pill :status="$tx['status']" size="lg"/>
        </div>
        @if($tx['status'] === 'DECLINED' && isset($tx['declineReason']))
        <div class="mt-2 text-sm" style="color: var(--color-danger);">{{ str_replace('_', ' ', $tx['declineReason']) }}</div>
        @endif
    </div>

    {{-- Details --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-3 font-semibold text-sm" style="border-bottom: 1px solid var(--color-border); color: var(--color-ink-mid);">{{ __('customer.transaction.details') }}</div>
        @php
        $rows = [
            [__('customer.transaction.type'), FormatService::txTypLabel($tx['type'])],
            [__('customer.transaction.counterparty'), $tx['counterparty'] ?? '—'],
            [__('customer.transaction.direction'), $tx['direction'] === 'in' ? __('customer.transaction.incoming') : __('customer.transaction.outgoing')],
        ];
        if ($tx['feeAmount'] > 0) $rows[] = [__('customer.transaction.fee'), FormatService::kmf($tx['feeAmount'])];
        if ($tx['netAmountToDestination'] > 0) $rows[] = [__('customer.transaction.net_amount'), FormatService::kmf($tx['netAmountToDestination'])];
        $rows[] = [__('customer.transaction.date'), FormatService::dateTime($tx['createdAt'])];
        if (isset($tx['completedAt'])) $rows[] = [__('customer.transaction.completed'), FormatService::dateTime($tx['completedAt'])];
        @endphp
        @foreach($rows as $row)
        <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <span style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
            <span style="font-size: 13px; font-weight: 500;">{{ $row[1] }}</span>
        </div>
        @endforeach
        <div class="flex justify-between items-center px-5 py-3">
            <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.transaction.transaction_id') }}</span>
            <div class="flex items-center gap-2">
                <span class="font-mono" style="font-size: 12px; color: var(--color-ink-mid);">{{ FormatService::shortId($tx['id']) }}</span>
                <button onclick="navigator.clipboard.writeText('{{ $tx['id'] }}')" class="circle-btn" style="width: 28px; height: 28px;">
                    <x-icon name="copy" class="w-3 h-3"/>
                </button>
            </div>
        </div>
    </div>

    @if(isset($tx['correlationId']))
    <div class="card-flat p-4 mb-5">
        <div class="section-title mb-1">{{ __('customer.transaction.correlation_id') }}</div>
        <div class="font-mono text-sm" style="color: var(--color-ink-mid);">{{ $tx['correlationId'] }}</div>
    </div>
    @endif

    @endif
</div>
