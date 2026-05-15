<?php

namespace App\Komopay\Services\Auth;

use App\Komopay\Contracts\MerchantAuthApi;
use App\Komopay\Http\KomopayHttpClient;

/** Real HTTP merchant auth — spec section 5.3. No refresh, no logout. */
final class HttpMerchantAuthApi implements MerchantAuthApi
{
    public function __construct(private readonly KomopayHttpClient $http) {}

    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
    {
        return (array) $this->http->post('auth/merchant/login', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
            'pin'              => $pin,
        ])->data;
    }

    public function verifyMfa(string $challengeId, string $code): array
    {
        return (array) $this->http->post('auth/merchant/login/verify-mfa', [
            'challengeId' => $challengeId,
            'code'        => $code,
        ])->data;
    }

    public function setupPin(string $pinSetupToken, string $pin): void
    {
        $this->http->post('auth/merchant/auth-pin/setup', ['pin' => $pin], [
            'bearer' => $pinSetupToken,
        ]);
    }

    public function changePin(string $accessToken, string $currentPin, string $newPin): void
    {
        $this->http->put('auth/merchant/auth-pin', [
            'currentPin' => $currentPin,
            'newPin'     => $newPin,
        ], ['bearer' => $accessToken]);
    }

    public function totpSetup(string $accessToken): array
    {
        return (array) $this->http->post('auth/merchant/totp-setup', [], ['bearer' => $accessToken])->data;
    }

    public function totpConfirm(string $accessToken, string $code): void
    {
        $this->http->post('auth/merchant/totp-confirm', ['code' => $code], ['bearer' => $accessToken]);
    }

    public function totpRevoke(string $accessToken, string $code): void
    {
        $this->http->delete('auth/merchant/totp-setup', ['code' => $code], ['bearer' => $accessToken]);
    }
}
