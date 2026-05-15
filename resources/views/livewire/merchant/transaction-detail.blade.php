@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('merchant.transactions') }}" class="circle-btn">
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </a>
        <h1 class="font-bold lg:!text-2xl" style="font-size: 19px; letter-spacing: -0.02em;">{{ __('merchant.transaction.detail_title') }}</h1>
    </div>

    @if(!$tx)
    <div class="empty-state">
        <x-icon name="warn" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
        <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">{{ __('merchant.transaction.not_found') }}</div>
        <a wire:navigate href="{{ route('merchant.transactions') }}" class="btn btn-secondary btn-md mt-2">{{ __('merchant.transaction.back_to_tx') }}</a>
    </div>
    @else

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main detail --}}
        <div class="lg:col-span-2">
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
                <div class="font-mono font-bold" style="font-size: 32px; letter-spacing: -0.02em; color: {{ $tx['direction'] === 'in' && $tx['status'] === 'COMPLETED' ? 'var(--color-success)' : ($tx['status'] === 'DECLINED' ? 'var(--color-danger)' : 'var(--color-ink-hi)') }};">
                    {{ $tx['direction'] === 'in' ? '+' : '−' }}{{ FormatService::kmf(abs($tx['requestedAmount'])) }}
                </div>
                <div class="mt-3 flex justify-center gap-2">
                    <x-tx-status-pill :status="$tx['status']" size="lg"/>
                    <span class="pill pill-neutral">{{ FormatService::txTypLabel($tx['type']) }}</span>
                </div>
                @if($tx['status'] === 'DECLINED' && isset($tx['declineReason']))
                <div class="mt-2 text-sm" style="color: var(--color-danger);">{{ str_replace('_', ' ', $tx['declineReason']) }}</div>
                @endif
            </div>

            {{-- Details table --}}
            <div class="card overflow-hidden mb-5">
                <div class="px-5 py-2 font-semibold text-sm" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border); color: var(--color-ink-mid);">{{ __('merchant.transaction.details') }}</div>
                @php
                $rows = [
                    [__('merchant.transaction.counterparty'), $tx['counterparty'] ?: '—'],
                    [__('merchant.transaction.type'), FormatService::txTypLabel($tx['type'])],
                    [__('merchant.transaction.direction'), $tx['direction'] === 'in' ? __('merchant.transaction.incoming') : __('merchant.transaction.outgoing')],
                    [__('merchant.transaction.operator'), $tx['operatorName'] ?? '—'],
                    [__('merchant.transaction.terminal'), $tx['terminalSerial'] ?? '—'],
                ];
                if (($tx['commissionAmount'] ?? 0) > 0) $rows[] = [__('merchant.transaction.commission'), FormatService::kmf($tx['commissionAmount'])];
                if (($tx['feeAmount'] ?? 0) > 0) $rows[] = [__('merchant.transaction.fee'), FormatService::kmf($tx['feeAmount'])];
                if (($tx['netAmountToDestination'] ?? 0) > 0) $rows[] = [__('merchant.transaction.net_to_dest'), FormatService::kmf($tx['netAmountToDestination'])];
                if (!empty($tx['createdAt'])) $rows[] = [__('merchant.transaction.created'), FormatService::dateTime($tx['createdAt'])];
                if (!empty($tx['completedAt'])) $rows[] = [__('merchant.transaction.completed'), FormatService::dateTime($tx['completedAt'])];
                @endphp
                @foreach($rows as $row)
                <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
                    <span style="font-size: 13px; font-weight: 500;">{{ $row[1] }}</span>
                </div>
                @endforeach
                <div class="flex justify-between items-center px-5 py-3">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('merchant.transaction.transaction_id') }}</span>
                    <div class="flex items-center gap-2">
                        <span class="font-mono" style="font-size: 12px; color: var(--color-ink-mid);">{{ FormatService::shortId($tx['id']) }}</span>
                        <button onclick="navigator.clipboard.writeText('{{ $tx['id'] }}')" class="circle-btn" style="width: 28px; height: 28px;">
                            <x-icon name="copy" class="w-3 h-3"/>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar info --}}
        <div>
            <div class="card p-5 mb-4">
                <div class="section-title mb-3">{{ __('merchant.transaction.wallet_ids') }}</div>
                <div class="mb-3">
                    <div style="font-size: 11px; color: var(--color-ink-low); margin-bottom: 2px;">{{ __('merchant.transaction.source') }}</div>
                    <div class="font-mono" style="font-size: 12px;">{{ !empty($tx['sourceWalletId']) ? FormatService::shortId($tx['sourceWalletId']) : '—' }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--color-ink-low); margin-bottom: 2px;">{{ __('merchant.transaction.destination') }}</div>
                    <div class="font-mono" style="font-size: 12px;">{{ !empty($tx['destinationWalletId']) ? FormatService::shortId($tx['destinationWalletId']) : '—' }}</div>
                </div>
            </div>
            <div class="card p-5">
                <div class="section-title mb-3">{{ __('merchant.transaction.initiator') }}</div>
                <div class="font-semibold" style="font-size: 14px;">{{ $tx['initiatorType'] }}</div>
                <div class="font-mono text-sm" style="color: var(--color-ink-low); margin-top: 2px;">{{ !empty($tx['initiatorId']) ? FormatService::shortId($tx['initiatorId']) : '—' }}</div>
            </div>
        </div>
    </div>
    @endif
</div>
