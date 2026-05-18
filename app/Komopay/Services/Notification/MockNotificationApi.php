<?php

namespace App\Komopay\Services\Notification;

use App\Komopay\Contracts\NotificationApi;
use App\Komopay\Exceptions\BusinessException;
use App\Komopay\Mock\Fixtures\NotificationFixtures;
use Illuminate\Contracts\Session\Session;

/**
 * Session-backed mock NotificationApi. State (read/unread, mark-all) is
 * persisted in the user session so the demo feels coherent between
 * requests without needing a database.
 *
 * Returns spec-conformant shapes (section 7.8). The `data` field is a
 * JSON string, not an object — same as the real backend.
 */
final class MockNotificationApi implements NotificationApi
{
    public function __construct(
        private readonly Session $session,
        /** Either 'customer' or 'merchant' — chooses the fixture set. */
        private readonly string $actor,
    ) {}

    public function list(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $rows  = $this->all();
        // Newest first — fixtures are not guaranteed sorted.
        usort($rows, fn ($a, $b) => strcmp($b['createdAt'], $a['createdAt']));
        return array_slice($rows, 0, $limit);
    }

    public function unreadCount(): int
    {
        return count(array_filter($this->all(), fn ($n) => $n['status'] === 'UNREAD'));
    }

    public function markRead(string $id): void
    {
        $rows  = $this->all();
        $found = false;
        foreach ($rows as &$row) {
            if ($row['id'] === $id) {
                $found = true;
                if ($row['status'] === 'UNREAD') {
                    $row['status'] = 'READ';
                    $row['readAt'] = gmdate('Y-m-d\TH:i:s\Z');
                }
                break;
            }
        }
        unset($row);

        if (!$found) {
            throw new BusinessException('Notification introuvable', 'NOT_FOUND', 404);
        }
        $this->session->put($this->key(), $rows);
    }

    public function markAllRead(): int
    {
        $rows    = $this->all();
        $updated = 0;
        $now     = gmdate('Y-m-d\TH:i:s\Z');
        foreach ($rows as &$row) {
            if ($row['status'] === 'UNREAD') {
                $row['status'] = 'READ';
                $row['readAt'] = $now;
                $updated++;
            }
        }
        unset($row);
        $this->session->put($this->key(), $rows);
        return $updated;
    }

    /** @return list<array> */
    private function all(): array
    {
        return $this->session->get($this->key(), $this->seed());
    }

    /** @return list<array> */
    private function seed(): array
    {
        return $this->actor === 'merchant'
            ? NotificationFixtures::forMerchant()
            : NotificationFixtures::forCustomer();
    }

    private function key(): string
    {
        return 'komopay.mock.notifications.' . $this->actor;
    }
}
