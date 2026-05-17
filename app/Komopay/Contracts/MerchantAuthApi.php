<?php

namespace App\Komopay\Contracts;

/**
 * Merchant auth surface — spec section 5.3.
 * No `refresh`, no `logout` (spec 3.4 — the merchant frontend can only clear local state).
 */
interface MerchantAuthApi
{
    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array;

    public function verifyMfa(string $challengeId, string $code): array;

    public function setupPin(string $pinSetupToken, string $pin): void;

    public function changePin(string $accessToken, string $currentPin, string $newPin): void;

    /** POST /auth/merchant/auth-pin/reset — 204. Public; gated by TOTP. */
    public function resetPin(string $phoneCountryCode, string $phoneNumber, string $totpCode, string $newPin): void;

    public function totpSetup(string $accessToken): array;

    public function totpConfirm(string $accessToken, string $code): void;

    public function totpRevoke(string $accessToken, string $code): void;
}
