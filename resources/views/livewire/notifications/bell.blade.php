<a wire:navigate href="{{ $this->inboxRoute() }}"
   class="circle-btn"
   style="position: relative;"
   wire:poll.30s="refresh"
   aria-label="{{ __('notifications.aria_open_inbox') }}">
    <x-icon name="bell" class="w-5 h-5"/>
    @if ($unread > 0)
        <span style="position: absolute; top: -2px; right: -2px; min-width: 18px; height: 18px; padding: 0 5px;
                     border-radius: 9px; background: var(--color-brand); color: #fff;
                     font-size: 10px; font-weight: 700; line-height: 18px; text-align: center;
                     box-shadow: 0 0 0 2px var(--color-bg);">
            {{ $unread > 99 ? '99+' : $unread }}
        </span>
    @endif
</a>
