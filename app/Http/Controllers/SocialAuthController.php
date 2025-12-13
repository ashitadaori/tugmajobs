<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobSeekerProfile;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect to social provider
     */
    public function redirectToProvider($provider, Request $request)
    {
        // Debug logging
        \Log::info('Social Auth Redirect Attempt', [
            'provider' => $provider,
            'provider_type' => gettype($provider),
            'provider_length' => strlen($provider),
            'provider_bytes' => bin2hex($provider),
            'is_google' => $provider === 'google',
            'equals_check' => $provider !== 'google' ? 'FAILED' : 'PASSED',
            'request_url' => $request->fullUrl(),
        ]);

        if ($provider !== 'google') {
            \Log::error('Invalid social provider', ['provider' => $provider, 'expected' => 'google']);
            return redirect()->route('home')->with('error', 'Invalid social provider.');
        }

        try {
            // Get the intended role from the request (jobseeker or employer)
            $intendedRole = $request->get('role', 'jobseeker');
            
            // Validate the role
            if (!in_array($intendedRole, ['jobseeker', 'employer'])) {
                $intendedRole = 'jobseeker';
            }
            
            // Store the intended role in session for use after callback
            session(['intended_role' => $intendedRole]);
            
            // Explicitly set the redirect URI to ensure it's included
            $redirectUri = config('services.google.redirect');
            
            // Log the configuration for debugging
            \Log::info('Google OAuth Configuration', [
                'client_id' => config('services.google.client_id'),
                'redirect_uri' => $redirectUri,
                'intended_role' => $intendedRole,
                'has_client_secret' => !empty(config('services.google.client_secret'))
            ]);
            
            return Socialite::driver('google')
                ->redirectUrl($redirectUri)
                ->scopes(['openid', 'email', 'profile'])
                ->redirect();
                
        } catch (\Exception $e) {
            \Log::error('Google OAuth redirect error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Google authentication is currently unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Handle callback from social provider
     */
    public function handleProviderCallback($provider)
    {
        if ($provider !== 'google') {
            return redirect()->route('home')->with('error', 'Invalid social provider.');
        }

        try {
            $socialUser = Socialite::driver('google')->user();
            
            // Check if user already exists with this social provider
            $existingUser = User::where('email', $socialUser->getEmail())->first();
            
            if ($existingUser) {
                // Update social provider info if not already set
                $this->updateSocialInfo($existingUser, $provider, $socialUser);

                // Fix role if it's null (for existing users who registered before role was properly set)
                if (is_null($existingUser->role)) {
                    $intendedRole = session('intended_role', 'jobseeker');
                    $existingUser->role = $intendedRole;
                    $existingUser->save();

                    // Create appropriate profile if missing
                    if ($intendedRole === 'jobseeker' && !$existingUser->jobSeekerProfile) {
                        JobSeekerProfile::create(['user_id' => $existingUser->id]);
                    } elseif ($intendedRole === 'employer' && !$existingUser->employer) {
                        Employer::create([
                            'user_id' => $existingUser->id,
                            'company_name' => 'Company Name',
                            'status' => 'pending',
                        ]);
                    }

                    \Log::info('Fixed null role for existing user', [
                        'user_id' => $existingUser->id,
                        'new_role' => $intendedRole
                    ]);
                }

                // Clear the intended role from session
                session()->forget('intended_role');

                Auth::login($existingUser, true); // Remember the user

                \Log::info('User logged in via Google OAuth', [
                    'user_id' => $existingUser->id,
                    'email' => $existingUser->email,
                    'role' => $existingUser->role,
                    'auth_check' => Auth::check(),
                    'session_id' => session()->getId()
                ]);

                return $this->redirectAfterLogin($existingUser);
            }

            // Create new user
            $user = $this->createUserFromSocial($socialUser, $provider);
            Auth::login($user, true); // Remember the user

            \Log::info('New user created and logged in via Google OAuth', [
                'user_id' => $user->id,
                'email' => $user->email,
                'auth_check' => Auth::check(),
                'session_id' => session()->getId()
            ]);

            return $this->redirectAfterLogin($user);
            
        } catch (\Exception $e) {
            \Log::error('Social authentication error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Create user from Google data
     */
    private function createUserFromSocial($socialUser, $provider)
    {
        // Get the intended role from session (set during OAuth redirect)
        $intendedRole = session('intended_role', 'jobseeker');
        
        // Clear the session data
        session()->forget('intended_role');
        
        $userData = [
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(32)), // Random password since they'll use Google login
            'role' => $intendedRole, // Use the intended role from session
            'profile_image' => $socialUser->getAvatar(),
            'google_id' => $socialUser->getId(),
            'google_token' => $socialUser->token,
        ];
        
        if ($socialUser->refreshToken) {
            $userData['google_refresh_token'] = $socialUser->refreshToken;
        }

        $user = User::create($userData);

        // Create appropriate profile based on role
        if ($user->isJobSeeker()) {
            JobSeekerProfile::create([
                'user_id' => $user->id,
            ]);
            \Log::info('Created JobSeekerProfile for Google OAuth user', ['user_id' => $user->id]);
        } elseif ($user->isEmployer()) {
            Employer::create([
                'user_id' => $user->id,
                'company_name' => 'Company Name', // Default, user will update later
                'status' => 'pending',
            ]);
            \Log::info('Created Employer profile for Google OAuth user', ['user_id' => $user->id]);
        }

        return $user;
    }

    /**
     * Update existing user's Google info
     */
    private function updateSocialInfo($user, $provider, $socialUser)
    {
        $updateData = [
            'google_id' => $socialUser->getId(),
            'google_token' => $socialUser->token,
        ];

        if ($socialUser->refreshToken) {
            $updateData['google_refresh_token'] = $socialUser->refreshToken;
        }

        // Update profile image if user doesn't have one
        if (!$user->profile_image && $socialUser->getAvatar()) {
            $updateData['profile_image'] = $socialUser->getAvatar();
        }

        $user->update($updateData);
    }

    /**
     * Redirect user after successful login
     */
    private function redirectAfterLogin($user)
    {
        // URL is now forced globally in AppServiceProvider, so route() will work correctly
        if ($user->isEmployer()) {
            \Log::info('Redirecting employer after Google login', [
                'email' => $user->email,
                'role' => $user->role,
                'url' => route('employer.dashboard')
            ]);
            return redirect()->route('employer.dashboard')->with('success', 'Welcome back!');
        }

        \Log::info('Redirecting jobseeker after Google login', [
            'email' => $user->email,
            'role' => $user->role,
            'url' => route('account.dashboard')
        ]);
        return redirect()->route('account.dashboard')->with('success', 'Welcome back!');
    }

    /**
     * Handle social login errors
     */
    public function handleError(Request $request)
    {
        $error = $request->get('error', 'Authentication cancelled');
        return redirect()->route('home')->with('error', 'Social login was cancelled or failed.');
    }
}