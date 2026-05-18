<?php

namespace App\Komopay\Contracts;

/**
 * Shared notifications inbox — spec section 5.5 (Customer + Merchant + Agent).
 *
 * The backend uses a single controller, scope-filtered server-side from the
 * JWT principal. One implementation is bound per actor in the service
 * provider so the caller never has to think about whose token to send.
 *
 * Errors are signalled by exceptions from App\Komopay\Exceptions\*.
 */
interface NotificationApi
{
    /**
     * GET /api/v1/notifications?limit — list<NotificationResponse> (spec 7.8).
     * Newest first. `limit` is clamped to [1, 100] by the backend.
     */
    public function list(int $limit = 20): array;

    /** GET /api/v1/notifications/unread — `{ unread: long }`. */
    public function unreadCount(): int;

    /**
     * POST /api/v1/notifications/{id}/read.
     * Idempotent; may raise BusinessException(404) for a foreign or unknown id.
     */
    public function markRead(string $id): void;

    /** POST /api/v1/notifications/read-all — returns the count of rows flipped. */
    public function markAllRead(): int;
}
