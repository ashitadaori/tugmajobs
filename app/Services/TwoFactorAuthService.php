<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for Google Authenticator
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get the QR code URL for Google Authenticator setup
     */
    public function getQRCodeUrl(User $user, string $secret): string
    {
        $appName = config('app.name', 'TugmaJobs');

        return $this->google2fa->getQRCodeUrl(
            $appName,
            $user->email,
            $secret
        );
    }

    /**
     * Generate QR code as SVG for display
     */
    public function getQRCodeSvg(User $user, string $secret): string
    {
        $qrCodeUrl = $this->getQRCodeUrl($user, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Verify a TOTP code from Google Authenticator
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        try {
            $secret = decrypt($user->two_factor_secret);
            return $this->google2fa->verifyKey($secret, $code);
        } catch (\Exception $e) {
            \Log::error('2FA verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify code during setup (before secret is saved to user)
     */
    public function verifySetupCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Enable 2FA for a user
     */
    public function enable(User $user, string $secret): bool
    {
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        return true;
    }

    /**
     * Disable 2FA for a user
     */
    public function disable(User $user): bool
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return true;
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(User $user): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10) . '-' . Str::random(10);
        }

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ]);

        return $codes;
    }

    /**
     * Verify a recovery code
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        try {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            if (in_array($code, $codes)) {
                // Remove used code
                $codes = array_diff($codes, [$code]);
                $user->update([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
                ]);
                return true;
            }
        } catch (\Exception $e) {
            \Log::error('Recovery code verification error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Get remaining recovery codes count
     */
    public function getRemainingRecoveryCodesCount(User $user): int
    {
        if (!$user->two_factor_recovery_codes) {
            return 0;
        }

        try {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            return count($codes);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if 2FA is required for a user
     */
    public function isRequired(User $user): bool
    {
        return $user->two_factor_enabled ?? false;
    }

    /**
     * Check if 2FA is enabled and confirmed for a user
     */
    public function isEnabled(User $user): bool
    {
        return $user->two_factor_enabled && $user->two_factor_confirmed_at !== null;
    }

    // ============================================
    // Legacy email-based 2FA methods (kept for backward compatibility)
    // ============================================

    /**
     * Generate and send a 2FA code to the user's email
     * @deprecated Use Google Authenticator instead
     */
    public function generateAndSendCode(User $user): bool
    {
        $code = $this->generateEmailCode();

        // Store the code in cache for 10 minutes
        $cacheKey = $this->getCacheKey($user->id);
        Cache::put($cacheKey, [
            'code' => $code,
            'attempts' => 0,
            'created_at' => now(),
        ], now()->addMinutes(10));

        // Send the code via email
        try {
            Mail::raw("Your verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Two-Factor Authentication Code');
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send 2FA code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify the email-based 2FA code
     * @deprecated Use Google Authenticator instead
     */
    public function verifyEmailCode(User $user, string $code): bool
    {
        $cacheKey = $this->getCacheKey($user->id);
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return false;
        }

        // Check if too many attempts
        if ($cached['attempts'] >= 5) {
            Cache::forget($cacheKey);
            return false;
        }

        // Update attempts
        $cached['attempts']++;
        Cache::put($cacheKey, $cached, now()->addMinutes(10));

        // Verify the code
        if ($cached['code'] === $code) {
            Cache::forget($cacheKey);
            return true;
        }

        return false;
    }

    /**
     * Generate a random 6-digit code for email
     */
    protected function generateEmailCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the cache key for the user's email 2FA code
     */
    protected function getCacheKey(int $userId): string
    {
        return "2fa_code_{$userId}";
    }
}
