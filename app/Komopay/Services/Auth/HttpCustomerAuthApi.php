<?php

namespace App\Komopay\Services\Auth;

use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Http\KomopayHttpClient;

/** Real HTTP customer auth — spec section 5.1. */
final class HttpCustomerAuthApi implements CustomerAuthApi
{
    public function __construct(private readonly KomopayHttpClient $http) {}

    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
    {
        return (array) $this->http->post('auth/customer/login', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
            'pin'              => $pin,
        ])->data;
    }

    public function verifyMfa(string $challengeId, string $code): array
    {
        return (array) $this->http->post('auth/customer/login/verify-mfa', [
            'challengeId' => $challengeId,
            'code'        => $code,
        ])->data;
    }

    public function refresh(string $refreshToken): array
    {
        return (array) $this->http->post('auth/customer/refresh', [
            'refreshToken' => $refreshToken,
        ])->data;
    }

    public function logout(string $accessToken): void
    {
        $this->http->post('auth/customer/logout', [], ['bearer' => $accessToken]);
    }

    public function setupPin(string $pinSetupToken, string $pin): void
    {
        $this->http->post('auth/customer/auth-pin/setup', ['pin' => $pin], [
            'bearer' => $pinSetupToken,
        ]);
    }

    public function changePin(string $accessToken, string $currentPin, string $newPin): void
    {
        $this->http->put('auth/customer/auth-pin', [
            'currentPin' => $currentPin,
            'newPin'     => $newPin,
        ], ['bearer' => $accessToken]);
    }

    public function totpSetup(string $accessToken): array
    {
        return (array) $this->http->post('auth/customer/totp-setup', [], ['bearer' => $accessToken])->data;
    }

    public function totpConfirm(string $accessToken, string $code): void
    {
        $this->http->post('auth/customer/totp-confirm', ['code' => $code], ['bearer' => $accessToken]);
    }

    public function totpRevoke(string $accessToken, string $code): void
    {
        $this->http->delete('auth/customer/totp-setup', ['code' => $code], ['bearer' => $accessToken]);
    }
}
