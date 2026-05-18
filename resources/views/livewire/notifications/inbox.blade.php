@php
use App\Services\FormatService;
$backRoute = $actor === 'merchant' ? route('merchant.dashboard') : route('customer.dashboard');
@endphp
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 lg:px-8 pt-5 lg:pt-8 pb-3">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ $backRoute }}" class="circle-btn" aria-label="{{ __('common.back') }}">
                <x-icon name="chev-left" class="w-5 h-5"/>
            </a>
            <div>
                <div class="text-sm" style="color: var(--color-ink-mid);">{{ __('notifications.subtitle') }}</div>
                <div class="font-bold lg:!text-2xl" style="font-size: 19px; letter-spacing: -0.015em; margin-top: 1px;">{{ __('notifications.title') }}</div>
            </div>
        </div>
        <div class="flex gap-2">
            <button wire:click="refresh" class="circle-btn" aria-label="{{ __('common.refresh') }}">
                <x-icon name="refresh" class="w-5 h-5"/>
            </button>
        </div>
    </div>

    @if ($error)
        <div class="mx-5 lg:mx-8 mb-3 px-4 py-3 rounded-xl"
             style="background: color-mix(in oklch, #ef4444 14%, transparent); color: #b91c1c; font-size: 13px;">
            {{ $error }}
        </div>
    @endif

    @if ($this->hasUnread())
        <div class="px-5 lg:px-8 mb-3 flex justify-end">
            <button wire:click="markAll"
                    class="text-xs font-semibold"
                    style="color: var(--color-brand); background: none; border: none; cursor: pointer;">
                {{ __('notifications.mark_all_read') }}
            </button>
        </div>
    @endif

    <div class="mx-5 lg:mx-8" wire:loading.class="opacity-60">
        @if (count($items) > 0)
        <div style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px; overflow: hidden;">
        @endif
        @forelse ($items as $i => $row)
            @php $unread = ($row['status'] ?? '') === 'UNREAD'; @endphp
            <button wire:click="open('{{ $row['id'] }}')" wire:key="ntf-{{ $row['id'] }}"
                    type="button"
                    class="w-full text-left flex items-start gap-3 px-4 py-3"
                    style="background: transparent; border: none; cursor: pointer;
                           {{ $i > 0 ? 'border-top: 1px solid var(--color-border);' : '' }}">
                <span style="margin-top: 7px; width: 8px; height: 8px; border-radius: 50%;
                             background: {{ $unread ? 'var(--color-brand)' : 'transparent' }};
                             flex-shrink: 0;"></span>
                <span class="flex-1 min-w-0">
                    <span class="flex items-baseline justify-between gap-3">
                        <span class="font-semibold truncate" style="font-size: 14px; {{ $unread ? '' : 'color: var(--color-ink-mid);' }}">
                            {{ $row['title'] ?? '' }}
                        </span>
                        <span style="font-size: 11px; color: var(--color-ink-low); flex-shrink: 0;">
                            {{ FormatService::relativeTime($row['createdAt'] ?? '') }}
                        </span>
                    </span>
                    <span class="block" style="font-size: 13px; color: var(--color-ink-mid); margin-top: 2px;">
                        {{ $row['body'] ?? '' }}
                    </span>
                </span>
            </button>
        @empty
            <div class="text-center py-16" style="color: var(--color-ink-mid);">
                <div style="width: 56px; height: 56px; border-radius: 50%;
                            background: color-mix(in oklch, var(--color-brand) 10%, transparent);
                            color: var(--color-brand);
                            display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                    <x-icon name="bell" class="w-6 h-6"/>
                </div>
                <div class="font-semibold" style="font-size: 15px;">{{ __('notifications.empty_title') }}</div>
                <div style="font-size: 13px; margin-top: 4px;">{{ __('notifications.empty_body') }}</div>
            </div>
        @endforelse
        @if (count($items) > 0)
        </div>
        @endif
    </div>
</div>
