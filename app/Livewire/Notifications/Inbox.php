<?php

namespace App\Livewire\Notifications;

use App\Komopay\Contracts\NotificationApi;
use App\Komopay\Exceptions\BusinessException;
use App\Komopay\Exceptions\KomopayException;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

/**
 * Notifications inbox — identical UX in Customer and Merchant portals
 * (spec 11.8). One component, mounted by both portals through their own
 * thin route + view wrapper that supplies $actor.
 */
class Inbox extends Component
{
    use HandlesAuthException;

    /** 'customer' | 'merchant' — set by the route component. */
    public string $actor = 'customer';

    /** @var list<array> NotificationResponse[] */
    public array $items = [];

    public ?string $error = null;

    public function mount(): void
    {
        $this->load();
    }

    public function load(): void
    {
        try {
            $this->items = $this->api()->list(20);
            $this->error = null;
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
        }
        $this->dispatch('notifications-updated');
    }

    /**
     * Tap a row: spec 11.8 step 3 — optimistic flip to READ, rollback on 4xx,
     * then deep-link based on the parsed `data` payload.
     */
    public function open(string $id): void
    {
        $original = $this->items;
        foreach ($this->items as &$row) {
            if ($row['id'] === $id && $row['status'] === 'UNREAD') {
                $row['status'] = 'READ';
                $row['readAt'] = gmdate('Y-m-d\TH:i:s\Z');
            }
        }
        unset($row);

        try {
            $this->api()->markRead($id);
        } catch (BusinessException $e) {
            // Rollback the optimistic flip — 403/404 means the row was foreign
            // or gone. Show the error inline and bail without deep-linking.
            $this->items = $original;
            $this->error = $e->getMessage();
            return;
        }
        $this->dispatch('notifications-updated');

        $target = $this->deepLinkFor($id);
        if ($target !== null) {
            $this->redirect($target, navigate: true);
        }
    }

    public function markAll(): void
    {
        try {
            $this->api()->markAllRead();
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
            return;
        }
        $this->load();
    }

    public function refresh(): void
    {
        $this->load();
    }

    public function hasUnread(): bool
    {
        foreach ($this->items as $row) {
            if ($row['status'] === 'UNREAD') {
                return true;
            }
        }
        return false;
    }

    public function render()
    {
        $layout = $this->actor === 'merchant' ? 'layouts.merchant' : 'layouts.customer';
        return view('livewire.notifications.inbox')
            ->layout($layout, ['title' => __('notifications.title')]);
    }

    private function api(): NotificationApi
    {
        return app('komopay.notifications.' . $this->actor);
    }

    /**
     * Spec 7.8 / 11.8: parse the JSON `data` string and route by `type`.
     * Returns null for unknown / payload-less rows (UI just dismisses).
     */
    private function deepLinkFor(string $id): ?string
    {
        foreach ($this->items as $row) {
            if ($row['id'] !== $id) {
                continue;
            }
            $raw = $row['data'] ?? null;
            if (!is_string($raw) || $raw === '') {
                return null;
            }
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return null;
            }
            $type = $decoded['type'] ?? null;
            $txId = $decoded['transactionId'] ?? null;
            if (!is_string($txId) || $txId === '') {
                return null;
            }

            $transactionTypes = ['P2P_TRANSFER', 'PAYMENT', 'CASH_IN', 'CASH_OUT', 'MERCHANT_TO_MERCHANT'];
            if (!in_array($type, $transactionTypes, true)) {
                return null;
            }

            $route = $this->actor === 'merchant' ? 'merchant.transactions.show' : 'customer.transactions.show';
            return route($route, ['id' => $txId]);
        }
        return null;
    }
}
