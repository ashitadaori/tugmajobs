<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TwoFactorAuthController extends Controller
{
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show the 2FA challenge form
     */
    public function show()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the 2FA code (Google Authenticator)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            // Clear 2FA session
            session()->forget('2fa_user_id');

            // Log the user in
            Auth::login($user, session('2fa_remember', false));
            session()->forget('2fa_remember');

            // Regenerate session
            $request->session()->regenerate();

            return redirect()->intended($this->redirectPath($user));
        }

        return back()->withErrors(['code' => 'The verification code is invalid. Please check your authenticator app.']);
    }

    /**
     * Verify using recovery code
     */
    public function verifyRecoveryCode(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        if ($this->twoFactorService->verifyRecoveryCode($user, $request->recovery_code)) {
            // Clear 2FA session
            session()->forget('2fa_user_id');

            // Log the user in
            Auth::login($user, session('2fa_remember', false));
            session()->forget('2fa_remember');

            // Regenerate session
            $request->session()->regenerate();

            $remainingCodes = $this->twoFactorService->getRemainingRecoveryCodesCount($user);

            return redirect()->intended($this->redirectPath($user))
                ->with('warning', "You used a recovery code. You have {$remainingCodes} recovery codes remaining. Please regenerate new recovery codes if needed.");
        }

        return back()->withErrors(['recovery_code' => 'The recovery code is invalid.']);
    }

    /**
     * Show the 2FA setup page (for enabling Google Authenticator)
     */
    public function showSetup()
    {
        $user = Auth::user();

        if ($this->twoFactorService->isEnabled($user)) {
            return redirect()->back()->with('info', 'Two-factor authentication is already enabled.');
        }

        // Generate a new secret key
        $secret = $this->twoFactorService->generateSecretKey();

        // Store it in session temporarily
        session(['2fa_setup_secret' => $secret]);

        // Generate QR code SVG
        $qrCodeSvg = $this->twoFactorService->getQRCodeSvg($user, $secret);

        return view('auth.two-factor-setup', [
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Confirm 2FA setup and enable it
     */
    public function confirmSetup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secret = session('2fa_setup_secret');

        if (!$secret) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please start the setup process again.',
            ], 422);
        }

        // Verify the code with the temporary secret
        if ($this->twoFactorService->verifySetupCode($secret, $request->code)) {
            // Enable 2FA with the secret
            $this->twoFactorService->enable($user, $secret);

            // Generate recovery codes
            $recoveryCodes = $this->twoFactorService->generateRecoveryCodes($user);

            // Clear the setup session
            session()->forget('2fa_setup_secret');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Two-factor authentication has been enabled.',
                    'recovery_codes' => $recoveryCodes,
                ]);
            }

            return redirect()->to($this->securitySettingsPath($user))
                ->with('success', 'Two-factor authentication has been enabled.')
                ->with('recovery_codes', $recoveryCodes);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code. Please try again.',
            ], 422);
        }

        return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The password is incorrect.',
                ], 422);
            }

            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        $this->twoFactorService->disable($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication has been disabled.',
            ]);
        }

        return redirect()->to($this->securitySettingsPath($user))
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The password is incorrect.',
                ], 422);
            }

            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Recovery codes have been regenerated.',
                'recovery_codes' => $recoveryCodes,
            ]);
        }

        return redirect()->to($this->securitySettingsPath($user))
            ->with('success', 'Recovery codes have been regenerated.')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Show recovery codes
     */
    public function showRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The password is incorrect.',
            ], 422);
        }

        if (!$user->two_factor_recovery_codes) {
            return response()->json([
                'success' => false,
                'message' => 'No recovery codes found.',
            ], 404);
        }

        try {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            return response()->json([
                'success' => true,
                'recovery_codes' => $codes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve recovery codes.',
            ], 500);
        }
    }

    /**
     * Get the redirect path based on user role
     */
    protected function redirectPath($user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        } elseif ($user->isEmployer()) {
            return route('employer.dashboard');
        } else {
            return route('account.dashboard');
        }
    }

    /**
     * Get the security settings path based on user role
     */
    protected function securitySettingsPath($user): string
    {
        if ($user->isAdmin()) {
            return route('admin.profile.security');
        } elseif ($user->isEmployer()) {
            return route('employer.settings.security');
        } else {
            return route('account.settings');
        }
    }
}
