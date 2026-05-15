@php use App\Services\FormatService; @endphp
<div>
    <div class="sticky top-0 z-10 px-5 lg:px-8 pt-5 lg:pt-8 pb-4" style="background: var(--color-bg);">
        <div class="flex items-center gap-3 mb-4">
            <a wire:navigate href="{{ route('customer.profile') }}" class="circle-btn">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
            <h1 class="font-bold flex-1 lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">{{ __('customer.statement.title') }}</h1>
            <button class="circle-btn">
                <x-icon name="download" class="w-4 h-4"/>
            </button>
        </div>
        {{-- Date filters --}}
        <div class="flex gap-2 lg:max-w-md">
            <div class="flex-1">
                <div class="label mb-1">{{ __('common.from') }}</div>
                <input wire:model="from" type="date" class="input" style="height: 42px; font-size: 13px;"/>
            </div>
            <div class="flex-1">
                <div class="label mb-1">{{ __('common.to') }}</div>
                <input wire:model="to" type="date" class="input" style="height: 42px; font-size: 13px;"/>
            </div>
        </div>
    </div>

    <div class="px-5 lg:px-8">
        @if(empty($entries))
        <div class="empty-state">
            <x-icon name="doc" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600;">{{ __('customer.statement.no_entries') }}</div>
        </div>
        @else
        <div class="card overflow-hidden">
            {{-- Header row --}}
            <div class="flex gap-3 px-4 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
                <span class="section-title flex-1">{{ __('customer.statement.description') }}</span>
                <span class="section-title text-right" style="width: 96px; flex-shrink: 0;">{{ __('customer.statement.amount') }}</span>
                <span class="section-title text-right hidden sm:block" style="width: 112px; flex-shrink: 0;">{{ __('customer.statement.balance') }}</span>
            </div>
            @foreach($entries as $entry)
            <div class="flex gap-3 items-center px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <div class="flex-1 min-w-0">
                    <div class="truncate" style="font-size: 13px; font-weight: 500;">{{ FormatService::txTypLabel(strtok($entry['description'], ' ')) }}</div>
                    <div style="font-size: 11px; color: var(--color-ink-low);">
                        {{ FormatService::dateTime($entry['postedAt'], 'd M · H:i') }}
                        <span class="sm:hidden font-mono" style="margin-left: 6px;">{{ __('customer.statement.bal_short') }} {{ FormatService::kmf($entry['runningBalance']) }}</span>
                    </div>
                </div>
                <div class="text-right" style="width: 96px; flex-shrink: 0;">
                    <span class="font-mono font-semibold" style="font-size: 13px; color: {{ $entry['entryType'] === 'CREDIT' ? 'var(--color-success)' : 'var(--color-ink-hi)' }};">
                        {{ $entry['entryType'] === 'CREDIT' ? '+' : '−' }}{{ FormatService::kmf($entry['amount']) }}
                    </span>
                </div>
                <div class="text-right hidden sm:block" style="width: 112px; flex-shrink: 0;">
                    <span class="font-mono" style="font-size: 12px; color: var(--color-ink-mid);">{{ FormatService::kmf($entry['runningBalance']) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    <div class="h-6"></div>
</div>
