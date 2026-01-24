<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Check if user wants password-based login or magic link
        $loginMethod = $request->input('login_method', 'magic_link');

        if ($loginMethod === 'password') {
            // Password-based login
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'min:5'],
            ]);

            // Find the user first to check 2FA before logging in
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return back()->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])->withInput($request->only('email'));
            }

            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                // Store user ID and remember preference in session for 2FA verification
                session([
                    '2fa_user_id' => $user->id,
                    '2fa_remember' => $request->has('remember'),
                ]);

                return redirect()->route('two-factor.challenge');
            }

            // No 2FA, proceed with normal login
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            // Redirect based on user role
            return $this->redirectBasedOnRole($user);

        } else {
            // Magic link login
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            // Check if user exists
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return redirect()->back()
                    ->with('error', 'No account found with this email address. Please register first.')
                    ->withInput($request->only('email'));
            }

            // Generate a secure token
            $token = Str::random(64);
            $expiresAt = now()->addMinutes(15);

            // Delete any existing unused tokens for this email
            DB::table('login_tokens')
                ->where('email', $request->email)
                ->where('used', false)
                ->delete();

            // Create new login token
            DB::table('login_tokens')->insert([
                'email' => $request->email,
                'token' => hash('sha256', $token),
                'role' => 'jobseeker',
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate the login URL
            $loginUrl = route('auth.verify-token', ['token' => $token]);

            // Send the email
            Mail::to($request->email)->send(new LoginCodeMail($loginUrl, 15));

            return redirect()->route('home')->with('success', 'Check your email! We sent you a sign-in link.');
        }
    }

    /**
     * Redirect based on user role
     */
    protected function redirectBasedOnRole(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->role === 'employer') {
            return redirect()->intended(route('employer.dashboard'));
        } else {
            return redirect()->intended(route('account.dashboard'));
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Get the user's role before logging out
        $wasAdmin = Auth::check() && Auth::user()->role === 'admin';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect admin to login page, others to home
        if ($wasAdmin) {
            return redirect()->route('login')->with('success', 'You have been successfully logged out.');
        }

        return redirect()->route('home')->with('success', 'You have been successfully logged out.');
    }
}
