<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employer;
use App\Models\Jobseeker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MagicLinkController extends Controller
{
    /**
     * Verify the login token and authenticate the user
     */
    public function verify(Request $request, $token)
    {
        // Hash the token to match what's stored in the database
        $hashedToken = hash('sha256', $token);

        // Find the token in the database
        $loginToken = DB::table('login_tokens')
            ->where('token', $hashedToken)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginToken) {
            return redirect()->route('home')->with('error', 'This sign-in link has expired or is invalid. Please request a new one.');
        }

        // Mark token as used
        DB::table('login_tokens')
            ->where('id', $loginToken->id)
            ->update(['used' => true]);

        // Check if user exists
        $user = User::where('email', $loginToken->email)->first();

        if ($user) {
            // Existing user - just log them in
            Auth::login($user, true); // Remember the user

            \Log::info('User logged in via magic link', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            return $this->redirectAfterLogin($user);
        }

        // New user - create account
        $user = User::create([
            'name' => 'User', // Default name, can be updated later
            'email' => $loginToken->email,
            'password' => Hash::make(Str::random(32)), // Random password
            'role' => $loginToken->role,
            'email_verified_at' => now(),
        ]);

        // Create appropriate profile
        if ($loginToken->role === 'jobseeker') {
            Jobseeker::create([
                'user_id' => $user->id,
            ]);
            \Log::info('Created new jobseeker via magic link', ['user_id' => $user->id]);
        } elseif ($loginToken->role === 'employer') {
            Employer::create([
                'user_id' => $user->id,
                'company_name' => 'Company Name', // Default, user will update later
                'status' => 'pending',
            ]);
            \Log::info('Created new employer via magic link', ['user_id' => $user->id]);
        }

        // Log the user in
        Auth::login($user, true); // Remember the user

        return $this->redirectAfterLogin($user);
    }

    /**
     * Redirect user after successful login
     */
    private function redirectAfterLogin($user)
    {
        // Admin can login from any login page
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'Welcome! You have been signed in successfully.');
        }

        if ($user->isEmployer()) {
            return redirect()->route('employer.dashboard')->with('success', 'Welcome! You have been signed in successfully.');
        }

        return redirect()->route('account.dashboard')->with('success', 'Welcome! You have been signed in successfully.');
    }
}
