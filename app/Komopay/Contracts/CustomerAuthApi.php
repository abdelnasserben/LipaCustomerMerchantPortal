<?php

namespace App\Komopay\Contracts;

/**
 * Customer auth surface — spec section 5.1.
 *
 * Returned arrays mirror the JSON envelopes from spec section 7.1
 * exactly (camelCase keys). UI code must not rely on any extra keys.
 */
interface CustomerAuthApi
{
    /** POST /auth/customer/login — returns LoginResponse (mfaRequired | pinSetupRequired | tokens). */
    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array;

    /** POST /auth/customer/login/verify-mfa — returns LoginResponse with tokens. */
    public function verifyMfa(string $challengeId, string $code): array;

    /** POST /auth/customer/refresh — returns TokenResponse. */
    public function refresh(string $refreshToken): array;

    /** POST /auth/customer/logout — 204. */
    public function logout(string $accessToken): void;

    /** POST /auth/customer/auth-pin/setup — 204. */
    public function setupPin(string $pinSetupToken, string $pin): void;

    /** PUT /auth/customer/auth-pin — 204. */
    public function changePin(string $accessToken, string $currentPin, string $newPin): void;

    /** POST /auth/customer/totp-setup — returns TotpSetupResponse. */
    public function totpSetup(string $accessToken): array;

    /** POST /auth/customer/totp-confirm — 204. */
    public function totpConfirm(string $accessToken, string $code): void;

    /** DELETE /auth/customer/totp-setup — 204. */
    public function totpRevoke(string $accessToken, string $code): void;
}
