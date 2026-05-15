<?php

namespace App\Komopay\Support;

use Illuminate\Support\Str;

/** Helper for the per-attempt Idempotency-Key on P2P / M2M / bill-pay (spec 2.4 / 11.3). */
final class IdempotencyKey
{
    public static function generate(): string
    {
        return (string) Str::uuid();
    }
}
