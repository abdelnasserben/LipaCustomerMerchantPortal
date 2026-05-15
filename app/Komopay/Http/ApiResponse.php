<?php

namespace App\Komopay\Http;

/**
 * Parsed success envelope.
 *
 * For ApiResponse<T> (spec 2.2):  data = T,           pagination = null
 * For PagedResponse<T>:           data = list<T>,     pagination = ['nextCursor','hasMore','limit']
 */
final class ApiResponse
{
    public function __construct(
        public readonly mixed $data,
        public readonly ?array $pagination = null,
        public readonly ?string $timestamp = null,
        public readonly int $httpStatus = 200,
        public readonly array $headers = [],
    ) {}

    public function isPaginated(): bool
    {
        return $this->pagination !== null;
    }

    public function nextCursor(): ?string
    {
        return $this->pagination['nextCursor'] ?? null;
    }

    public function hasMore(): bool
    {
        return (bool) ($this->pagination['hasMore'] ?? false);
    }
}
