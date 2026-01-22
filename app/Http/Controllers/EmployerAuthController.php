<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\LoginCodeMail;

class EmployerAuthController extends Controller
{
    /**
     * Show employer login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isEmployer()) {
                return redirect()->route('employer.dashboard');
            }
            return redirect()->route('account.dashboard');
        }

        return view('auth.employer-login');
    }

    /**
     * Show employer registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isEmployer()) {
                return redirect()->route('employer.dashboard');
            }
            return redirect()->route('account.dashboard');
        }

        return view('auth.employer-register');
    }

    /**
     * Handle employer login
     */
    public function login(Request $request)
    {
        // Check if user wants password-based login or magic link
        $loginMethod = $request->input('login_method', 'magic_link');

        if ($loginMethod === 'password') {
            // Password-based login
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:5',
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
                'email' => 'required|email',
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
                'role' => 'employer',
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
     * Handle employer registration
     */
    public function register(Request $request)
    {
        // Check if user wants password-based registration or magic link
        $registrationMethod = $request->input('registration_method', 'magic_link');

        if ($registrationMethod === 'password') {
            // Password-based registration
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:5|same:confirm_password',
                'confirm_password' => 'required',
            ], [
                'email.unique' => 'The email is already in use.'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->only('email', 'name'));
            }

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employer',
                'email_verified_at' => now(),
            ]);

            // Create employer profile
            Employer::create([
                'user_id' => $user->id,
                'company_name' => $request->name, // Use name as default company name
                'status' => 'pending',
            ]);

            // Log the user in
            Auth::login($user, true);

            return redirect()->route('employer.dashboard')->with('success', 'Welcome! Your employer account has been created successfully.');

        } else {
            // Magic link registration
            $request->validate([
                'email' => 'required|email',
            ]);

            // Generate a secure token
            $token = Str::random(64);
            $expiresAt = now()->addMinutes(15);

            // Delete any existing unused tokens for this email
            DB::table('login_tokens')
                ->where('email', $request->email)
                ->where('used', false)
                ->delete();

            // Create new login token for registration
            DB::table('login_tokens')->insert([
                'email' => $request->email,
                'token' => hash('sha256', $token),
                'role' => 'employer',
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
}
