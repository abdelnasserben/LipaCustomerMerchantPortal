<?php

namespace App\Komopay\Support;

use Illuminate\Contracts\Session\Session;

/**
 * Reads/writes the active JWT envelope for one actor type.
 *
 * Customer envelope: { accessToken, accessTokenExpiresAt, refreshToken, refreshTokenExpiresAt }
 * Merchant envelope: same shape, but no usable refreshToken (spec 3.4).
 */
class TokenStore
{
    public function __construct(
        private readonly Session $session,
        private readonly string $key,
    ) {}

    public function put(array $tokens): void
    {
        $this->session->put($this->key, $tokens);
    }

    public function get(): ?array
    {
        return $this->session->get($this->key);
    }

    public function accessToken(): ?string
    {
        return $this->get()['accessToken'] ?? null;
    }

    public function refreshToken(): ?string
    {
        return $this->get()['refreshToken'] ?? null;
    }

    public function clear(): void
    {
        $this->session->forget($this->key);
    }
}
