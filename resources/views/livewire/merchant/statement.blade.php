@php use App\Services\FormatService; @endphp
<div class="p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-bold" style="font-size: 22px; letter-spacing: -0.02em;">Statement</h1>
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">Ledger entries with running balance</div>
        </div>
        <div class="flex gap-3">
            <div class="flex gap-2">
                <input wire:model="from" type="date" class="input" style="height: 40px; font-size: 13px; width: auto;"/>
                <span class="flex items-center" style="color: var(--color-ink-low);">to</span>
                <input wire:model="to" type="date" class="input" style="height: 40px; font-size: 13px; width: auto;"/>
            </div>
            <button class="btn btn-secondary btn-md">
                <x-icon name="download" class="w-4 h-4"/>Export
            </button>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="grid grid-cols-12 px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="col-span-1 section-title">#</div>
            <div class="col-span-5 section-title">Description</div>
            <div class="col-span-2 section-title">Date</div>
            <div class="col-span-2 section-title text-right">Amount</div>
            <div class="col-span-2 section-title text-right">Balance</div>
        </div>

        @forelse($entries as $i => $entry)
        <div class="grid grid-cols-12 items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <div class="col-span-1">
                <span class="font-mono" style="font-size: 11px; color: var(--color-ink-low);">{{ $entry['globalSequence'] ?? ($i + 1) }}</span>
            </div>
            <div class="col-span-5">
                <div style="font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $entry['description'] }}</div>
                <div class="font-mono" style="font-size: 11px; color: var(--color-ink-low);">{{ FormatService::shortId($entry['transactionId']) }}</div>
            </div>
            <div class="col-span-2" style="font-size: 12px; color: var(--color-ink-low);">
                {{ FormatService::dateTime($entry['postedAt'], 'd M · H:i') }}
            </div>
            <div class="col-span-2 text-right">
                <span class="font-mono font-semibold" style="font-size: 13px; color: {{ $entry['entryType'] === 'CREDIT' ? 'var(--color-success)' : 'var(--color-danger)' }};">
                    {{ $entry['entryType'] === 'CREDIT' ? '+' : '−' }}{{ FormatService::kmf($entry['amount']) }}
                </span>
            </div>
            <div class="col-span-2 text-right">
                <span class="font-mono" style="font-size: 12px; color: var(--color-ink-mid);">{{ FormatService::kmf($entry['runningBalance']) }}</span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <x-icon name="doc" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600;">No statement entries</div>
        </div>
        @endforelse
    </div>
</div>
