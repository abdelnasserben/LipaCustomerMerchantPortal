@php use App\Services\FormatService; @endphp
<div class="p-6 lg:p-8">
    <div class="flex items-center gap-4 mb-6">
        <a wire:navigate href="{{ route('merchant.transactions') }}" class="btn btn-secondary btn-sm">
            <x-icon name="arrow-left" class="w-4 h-4"/>Back
        </a>
        <h1 class="font-bold" style="font-size: 22px; letter-spacing: -0.02em;">Transaction Detail</h1>
    </div>

    @if(!$tx)
    <div class="empty-state">
        <x-icon name="warn" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
        <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">Transaction not found</div>
        <a wire:navigate href="{{ route('merchant.transactions') }}" class="btn btn-secondary btn-md mt-2">Back to Transactions</a>
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
                <div class="px-5 py-2 font-semibold text-sm" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border); color: var(--color-ink-mid);">Transaction Details</div>
                @php
                $rows = [
                    ['Counterparty', $tx['counterparty']],
                    ['Type', FormatService::txTypLabel($tx['type'])],
                    ['Direction', $tx['direction'] === 'in' ? 'Incoming (credit)' : 'Outgoing (debit)'],
                    ['Operator', $tx['operatorName'] ?? '—'],
                    ['Terminal', $tx['terminalSerial'] ?? '—'],
                ];
                if ($tx['commissionAmount'] > 0) $rows[] = ['Commission', FormatService::kmf($tx['commissionAmount'])];
                if ($tx['feeAmount'] > 0) $rows[] = ['Fee', FormatService::kmf($tx['feeAmount'])];
                if ($tx['netAmountToDestination'] > 0) $rows[] = ['Net to destination', FormatService::kmf($tx['netAmountToDestination'])];
                $rows[] = ['Created', FormatService::dateTime($tx['createdAt'])];
                if (isset($tx['completedAt'])) $rows[] = ['Completed', FormatService::dateTime($tx['completedAt'])];
                @endphp
                @foreach($rows as $row)
                <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
                    <span style="font-size: 13px; font-weight: 500;">{{ $row[1] }}</span>
                </div>
                @endforeach
                <div class="flex justify-between items-center px-5 py-3">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">Transaction ID</span>
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
                <div class="section-title mb-3">Wallet IDs</div>
                <div class="mb-3">
                    <div style="font-size: 11px; color: var(--color-ink-low); margin-bottom: 2px;">Source</div>
                    <div class="font-mono" style="font-size: 12px;">{{ FormatService::shortId($tx['sourceWalletId']) }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--color-ink-low); margin-bottom: 2px;">Destination</div>
                    <div class="font-mono" style="font-size: 12px;">{{ $tx['destinationWalletId'] ? FormatService::shortId($tx['destinationWalletId']) : '—' }}</div>
                </div>
            </div>
            <div class="card p-5">
                <div class="section-title mb-3">Initiator</div>
                <div class="font-semibold" style="font-size: 14px;">{{ $tx['initiatorType'] }}</div>
                <div class="font-mono text-sm" style="color: var(--color-ink-low); margin-top: 2px;">{{ FormatService::shortId($tx['initiatorId']) }}</div>
            </div>
        </div>
    </div>
    @endif
</div>
