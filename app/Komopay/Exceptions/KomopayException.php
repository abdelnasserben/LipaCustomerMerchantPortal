<?php

namespace App\Komopay\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception for every KomoPay API failure (HTTP or mock).
 *
 * `code`           — backend ErrorCode string (e.g. "INSUFFICIENT_BALANCE").
 * `httpStatus`     — HTTP status the backend returned (0 for transport errors).
 * `details`        — list of "field: message" strings (validation errors).
 * `correlationId`  — server-side correlation id for support requests.
 */
class KomopayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'UNKNOWN',
        public readonly int $httpStatus = 0,
        public readonly array $details = [],
        public readonly ?string $correlationId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }
}
