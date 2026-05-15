<?php

namespace App\Komopay\Contracts;

/** Plain DTO for PagedResponse<T> (spec 2.2). `items` is always a list of arrays. */
final class CursorPage
{
    public function __construct(
        public readonly array $items,
        public readonly ?string $nextCursor,
        public readonly bool $hasMore,
        public readonly int $limit,
    ) {}

    public static function empty(int $limit = 20): self
    {
        return new self([], null, false, $limit);
    }
}
