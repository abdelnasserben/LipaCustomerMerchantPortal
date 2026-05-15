@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-5 lg:mb-6">
        <div>
            <h1 class="font-bold lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">Statement</h1>
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">Ledger entries with running balance</div>
        </div>
        <div class="flex gap-2 items-center w-full sm:w-auto">
            <input wire:model="from" type="date" class="input flex-1 sm:flex-none" style="height: 40px; font-size: 13px; min-width: 0; padding: 0 10px; width: auto;"/>
            <span class="flex-shrink-0" style="color: var(--color-ink-low);">→</span>
            <input wire:model="to" type="date" class="input flex-1 sm:flex-none" style="height: 40px; font-size: 13px; min-width: 0; padding: 0 10px; width: auto;"/>
            <button class="btn btn-secondary btn-sm flex-shrink-0">
                <x-icon name="download" class="w-4 h-4"/><span class="hidden sm:inline">Export</span>
            </button>
        </div>
    </div>

    <div class="card overflow-hidden">
        {{-- Desktop header --}}
        <div class="hidden md:grid grid-cols-12 px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="col-span-1 section-title">#</div>
            <div class="col-span-5 section-title">Description</div>
            <div class="col-span-2 section-title">Date</div>
            <div class="col-span-2 section-title text-right">Amount</div>
            <div class="col-span-2 section-title text-right">Balance</div>
        </div>

        @forelse($entries as $i => $entry)
        <div class="flex md:grid md:grid-cols-12 items-center gap-3 px-4 md:px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <div class="hidden md:block md:col-span-1">
                <span class="font-mono" style="font-size: 11px; color: var(--color-ink-low);">{{ $entry['globalSequence'] ?? ($i + 1) }}</span>
            </div>
            <div class="md:col-span-5 flex-1 min-w-0">
                <div class="truncate" style="font-size: 13px; font-weight: 500;">{{ FormatService::txTypLabel(strtok((string)($entry['description'] ?? ''), ' ') ?: '') }}</div>
                <div class="font-mono" style="font-size: 11px; color: var(--color-ink-low);">
                    {{ !empty($entry['transactionId']) ? FormatService::shortId($entry['transactionId']) : '—' }}
                    <span class="md:hidden"> · {{ !empty($entry['postedAt']) ? FormatService::dateTime($entry['postedAt'], 'd M · H:i') : '—' }}</span>
                </div>
            </div>
            <div class="hidden md:block md:col-span-2" style="font-size: 12px; color: var(--color-ink-low);">
                {{ !empty($entry['postedAt']) ? FormatService::dateTime($entry['postedAt'], 'd M · H:i') : '—' }}
            </div>
            <div class="md:col-span-2 text-right flex-shrink-0">
                <span class="font-mono font-semibold" style="font-size: 13px; color: {{ ($entry['entryType'] ?? '') === 'CREDIT' ? 'var(--color-success)' : 'var(--color-danger)' }};">
                    {{ ($entry['entryType'] ?? '') === 'CREDIT' ? '+' : '−' }}{{ FormatService::kmf((int)($entry['amount'] ?? 0)) }}
                </span>
                <div class="md:hidden font-mono" style="font-size: 11px; color: var(--color-ink-low); margin-top: 2px;">bal {{ FormatService::kmf((int)($entry['runningBalance'] ?? 0)) }}</div>
            </div>
            <div class="hidden md:block md:col-span-2 text-right">
                <span class="font-mono" style="font-size: 12px; color: var(--color-ink-mid);">{{ FormatService::kmf((int)($entry['runningBalance'] ?? 0)) }}</span>
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
