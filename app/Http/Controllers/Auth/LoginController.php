<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->has('remember'))) {
                $request->session()->regenerate();
                $user = Auth::user();

                // Admin can login from any login page
                // Redirect based on user role
                if ($user->role === 'admin') {
                    return redirect()->intended(route('admin.dashboard'));
                } elseif ($user->role === 'employer') {
                    return redirect()->intended(route('employer.dashboard'));
                } else {
                    return redirect()->intended(route('account.dashboard'));
                }
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email'));

        } else {
            // Magic link login
            $request->validate([
                'email' => ['required', 'email'],
            ]);

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