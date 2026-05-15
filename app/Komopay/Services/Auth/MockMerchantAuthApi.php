<?php

namespace App\Komopay\Services\Auth;

use App\Komopay\Contracts\MerchantAuthApi;
use App\Komopay\Exceptions\AuthException;
use App\Komopay\Exceptions\BusinessException;
use Illuminate\Support\Str;

/** Mock merchant auth — same well-known PIN branches as the customer mock. */
final class MockMerchantAuthApi implements MerchantAuthApi
{
    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
    {
        if ($pin === '0000') {
            throw new BusinessException('PIN locked for 15 minutes', 'AUTH_PIN_LOCKED', 422);
        }
        if ($pin === '2222') {
            return [
                'mfaRequired'      => true,
                'challengeId'      => (string) Str::uuid(),
                'mfaFactor'        => 'TOTP',
                'pinSetupRequired' => false,
            ];
        }
        if ($pin === '3333') {
            return [
                'mfaRequired'             => false,
                'pinSetupRequired'        => true,
                'pinSetupToken'           => 'mock-pin-setup-token',
                'pinSetupTokenExpiresAt'  => now()->addMinutes(10)->toIso8601String(),
            ];
        }
        return [
            'mfaRequired'      => false,
            'pinSetupRequired' => false,
            'tokens'           => $this->fakeTokens(),
        ];
    }

    public function verifyMfa(string $challengeId, string $code): array
    {
        if (strlen($code) !== 6) {
            throw new AuthException('Invalid MFA code', 'MFA_INVALID', 401);
        }
        return [
            'mfaRequired'      => false,
            'pinSetupRequired' => false,
            'tokens'           => $this->fakeTokens(),
        ];
    }

    public function setupPin(string $pinSetupToken, string $pin): void
    {
        if (strlen($pin) < 4 || strlen($pin) > 8) {
            throw new BusinessException('Invalid PIN format', 'AUTH_PIN_FORMAT', 422);
        }
    }

    public function changePin(string $accessToken, string $currentPin, string $newPin): void
    {
        if (strlen($newPin) < 4 || strlen($newPin) > 8) {
            throw new BusinessException('Invalid PIN format', 'AUTH_PIN_FORMAT', 422);
        }
    }

    public function totpSetup(string $accessToken): array
    {
        return [
            'secret' => 'MFZWI3DZEBVGK3TF',
            'qrUri'  => 'otpauth://totp/Lipa:merchant?secret=MFZWI3DZEBVGK3TF&issuer=Lipa',
        ];
    }

    public function totpConfirm(string $accessToken, string $code): void
    {
        if (strlen($code) !== 6) {
            throw new AuthException('Invalid TOTP code', 'MFA_INVALID', 401);
        }
    }

    public function totpRevoke(string $accessToken, string $code): void
    {
        if (strlen($code) !== 6) {
            throw new AuthException('Invalid TOTP code', 'MFA_INVALID', 401);
        }
    }

    private function fakeTokens(): array
    {
        return [
            'accessToken'           => 'mock-merchant-access-' . Str::random(24),
            'accessTokenExpiresAt'  => now()->addHours(8)->toIso8601String(),
            // Spec 3.4: merchant has no usable refresh — keep field shape for parity.
            'refreshToken'          => '',
            'refreshTokenExpiresAt' => now()->addHours(8)->toIso8601String(),
        ];
    }
}
