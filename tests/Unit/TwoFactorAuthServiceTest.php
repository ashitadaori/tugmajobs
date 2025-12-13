<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TwoFactorAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TwoFactorAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TwoFactorAuthService();
    }

    /**
     * Test can generate 2FA code.
     */
    public function test_can_generate_2fa_code()
    {
        $user = User::factory()->create();

        $code = $this->service->generateCode($user);

        $this->assertNotNull($code);
        $this->assertEquals(6, strlen($code));
        $this->assertIsNumeric($code);
    }

    /**
     * Test generated code is cached.
     */
    public function test_generated_code_is_cached()
    {
        $user = User::factory()->create();

        $code = $this->service->generateCode($user);

        $cachedCode = Cache::get("2fa_code_{$user->id}");
        $this->assertEquals($code, $cachedCode);
    }

    /**
     * Test can verify correct code.
     */
    public function test_can_verify_correct_code()
    {
        $user = User::factory()->create();
        $code = $this->service->generateCode($user);

        $isValid = $this->service->verifyCode($user, $code);

        $this->assertTrue($isValid);
    }

    /**
     * Test cannot verify incorrect code.
     */
    public function test_cannot_verify_incorrect_code()
    {
        $user = User::factory()->create();
        $this->service->generateCode($user);

        $isValid = $this->service->verifyCode($user, '000000');

        $this->assertFalse($isValid);
    }

    /**
     * Test code is cleared after successful verification.
     */
    public function test_code_cleared_after_verification()
    {
        $user = User::factory()->create();
        $code = $this->service->generateCode($user);

        $this->service->verifyCode($user, $code);

        $cachedCode = Cache::get("2fa_code_{$user->id}");
        $this->assertNull($cachedCode);
    }

    /**
     * Test can enable 2FA for user.
     */
    public function test_can_enable_2fa_for_user()
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);

        $recoveryCodes = $this->service->enable($user);

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotEmpty($recoveryCodes);
        $this->assertIsArray($recoveryCodes);
    }

    /**
     * Test can disable 2FA for user.
     */
    public function test_can_disable_2fa_for_user()
    {
        $user = User::factory()->create(['two_factor_enabled' => true]);

        $this->service->disable($user);

        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    /**
     * Test can generate recovery codes.
     */
    public function test_can_generate_recovery_codes()
    {
        $user = User::factory()->create();

        $codes = $this->service->generateRecoveryCodes($user);

        $this->assertCount(8, $codes);
        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{5}-[a-zA-Z0-9]{5}$/', $code);
        }
    }

    /**
     * Test can verify recovery code.
     */
    public function test_can_verify_recovery_code()
    {
        $user = User::factory()->create();
        $codes = $this->service->generateRecoveryCodes($user);

        $isValid = $this->service->verifyRecoveryCode($user, $codes[0]);

        $this->assertTrue($isValid);
    }

    /**
     * Test recovery code is consumed after use.
     */
    public function test_recovery_code_consumed_after_use()
    {
        $user = User::factory()->create();
        $codes = $this->service->generateRecoveryCodes($user);
        $usedCode = $codes[0];

        $this->service->verifyRecoveryCode($user, $usedCode);

        // Try to use the same code again
        $isValid = $this->service->verifyRecoveryCode($user, $usedCode);
        $this->assertFalse($isValid);
    }

    /**
     * Test cannot verify invalid recovery code.
     */
    public function test_cannot_verify_invalid_recovery_code()
    {
        $user = User::factory()->create();
        $this->service->generateRecoveryCodes($user);

        $isValid = $this->service->verifyRecoveryCode($user, 'XXXXX-XXXXX');

        $this->assertFalse($isValid);
    }

    /**
     * Test checks if 2FA is enabled.
     */
    public function test_checks_if_2fa_enabled()
    {
        $userWith2FA = User::factory()->create(['two_factor_enabled' => true]);
        $userWithout2FA = User::factory()->create(['two_factor_enabled' => false]);

        $this->assertTrue($this->service->isEnabled($userWith2FA));
        $this->assertFalse($this->service->isEnabled($userWithout2FA));
    }
}
