<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Verify the 2FA code
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

        return back()->withErrors(['code' => 'The verification code is invalid or expired.']);
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

            return redirect()->intended($this->redirectPath($user))
                ->with('warning', 'You used a recovery code. Please regenerate new recovery codes.');
        }

        return back()->withErrors(['recovery_code' => 'The recovery code is invalid.']);
    }

    /**
     * Resend 2FA code
     */
    public function resend()
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        if ($this->twoFactorService->generateAndSendCode($user)) {
            return back()->with('success', 'A new verification code has been sent to your email.');
        }

        return back()->with('error', 'Failed to send verification code. Please try again.');
    }

    /**
     * Enable 2FA for the authenticated user
     */
    public function enable(Request $request)
    {
        $user = Auth::user();

        // Send initial verification code
        if ($this->twoFactorService->generateAndSendCode($user)) {
            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send verification code.',
        ], 500);
    }

    /**
     * Confirm 2FA enablement
     */
    public function confirmEnable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            $this->twoFactorService->enable($user);
            $recoveryCodes = $this->twoFactorService->generateRecoveryCodes($user);

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication has been enabled.',
                'recovery_codes' => $recoveryCodes,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code.',
        ], 422);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $this->twoFactorService->disable($user);

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication has been disabled.',
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes($user);

        return response()->json([
            'success' => true,
            'message' => 'Recovery codes have been regenerated.',
            'recovery_codes' => $recoveryCodes,
        ]);
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
}
