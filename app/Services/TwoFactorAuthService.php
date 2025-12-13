<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    /**
     * Generate and send a 2FA code to the user's email
     */
    public function generateAndSendCode(User $user): bool
    {
        $code = $this->generateCode();

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
     * Verify the 2FA code
     */
    public function verifyCode(User $user, string $code): bool
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
     * Enable 2FA for a user
     */
    public function enable(User $user): bool
    {
        $user->update([
            'two_factor_enabled' => true,
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

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (in_array($code, $codes)) {
            // Remove used code
            $codes = array_diff($codes, [$code]);
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Check if 2FA is required for a user
     */
    public function isRequired(User $user): bool
    {
        return $user->two_factor_enabled ?? false;
    }

    /**
     * Generate a random 6-digit code
     */
    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the cache key for the user's 2FA code
     */
    protected function getCacheKey(int $userId): string
    {
        return "2fa_code_{$userId}";
    }
}
