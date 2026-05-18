<?php

namespace App\Livewire\Notifications;

use App\Komopay\Contracts\NotificationApi;
use App\Komopay\Exceptions\KomopayException;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Notifications bell — small badge component meant to live in the portal
 * header. Polls /api/v1/notifications/unread on the cadence recommended by
 * spec 11.8 (30s while foregrounded), and re-fetches when other components
 * dispatch the `notifications-updated` event after marking rows read.
 */
class Bell extends Component
{
    use HandlesAuthException;

    /** 'customer' | 'merchant' — set by the layout that mounts the bell. */
    public string $actor = 'customer';

    public int $unread = 0;

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('notifications-updated')]
    public function refresh(): void
    {
        try {
            $this->unread = $this->api()->unreadCount();
        } catch (KomopayException) {
            // Silent: badge is best-effort. AuthException is handled by the trait.
        }
    }

    public function inboxRoute(): string
    {
        return $this->actor === 'merchant'
            ? route('merchant.notifications')
            : route('customer.notifications');
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }

    private function api(): NotificationApi
    {
        return app('komopay.notifications.' . $this->actor);
    }
}
