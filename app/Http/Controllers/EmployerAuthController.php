<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
                'email' => 'required|email',
            ]);

            // Generate a secure token
            $token = \Illuminate\Support\Str::random(64);
            $expiresAt = now()->addMinutes(15);

            // Delete any existing unused tokens for this email
            \Illuminate\Support\Facades\DB::table('login_tokens')
                ->where('email', $request->email)
                ->where('used', false)
                ->delete();

            // Create new login token
            \Illuminate\Support\Facades\DB::table('login_tokens')->insert([
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
            \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\LoginCodeMail($loginUrl, 15));

            return redirect()->route('home')->with('success', 'Check your email! We sent you a sign-in link.');
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
            $token = \Illuminate\Support\Str::random(64);
            $expiresAt = now()->addMinutes(15);

            // Delete any existing unused tokens for this email
            \Illuminate\Support\Facades\DB::table('login_tokens')
                ->where('email', $request->email)
                ->where('used', false)
                ->delete();

            // Create new login token for registration
            \Illuminate\Support\Facades\DB::table('login_tokens')->insert([
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
            \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\LoginCodeMail($loginUrl, 15));

            return redirect()->route('home')->with('success', 'Check your email! We sent you a sign-in link.');
        }
    }
}