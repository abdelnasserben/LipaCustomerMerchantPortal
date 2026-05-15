<?php

namespace App\Komopay\Services\Auth;

use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Exceptions\AuthException;
use App\Komopay\Exceptions\BusinessException;
use Illuminate\Support\Str;

/**
 * Mock customer auth — drives the three LoginResponse branches via well-known PINs:
 *
 *   pin = "0000"  → AUTH_PIN_LOCKED  (BusinessException 422)
 *   pin = "2222"  → mfaRequired
 *   pin = "3333"  → pinSetupRequired
 *   anything else → full session (`tokens`)
 *
 * MFA verify and PIN setup always succeed. TOTP enrollment returns a
 * deterministic secret/QR uri so the same QR renders across reloads.
 */
final class MockCustomerAuthApi implements CustomerAuthApi
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

    public function refresh(string $refreshToken): array
    {
        return $this->fakeTokens();
    }

    public function logout(string $accessToken): void {}

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
            'secret' => 'JBSWY3DPEHPK3PXP',
            'qrUri'  => 'otpauth://totp/Lipa:customer?secret=JBSWY3DPEHPK3PXP&issuer=Lipa',
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
            'accessToken'           => 'mock-access-' . Str::random(24),
            'accessTokenExpiresAt'  => now()->addMinutes(15)->toIso8601String(),
            'refreshToken'          => 'mock-refresh-' . Str::random(24),
            'refreshTokenExpiresAt' => now()->addDays(30)->toIso8601String(),
        ];
    }
}
