<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jobseeker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/account/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // Role is automatically assigned as 'jobseeker' for regular registration
        ]);
    }

    public function register(Request $request)
    {
        // Check if user wants password-based registration or magic link
        $registrationMethod = $request->input('registration_method', 'magic_link');

        if ($registrationMethod === 'password') {
            // Password-based registration
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['nullable', 'in:jobseeker,employer'],
            ], [
                'name.required' => 'Please enter your name.',
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already registered.',
                'password.required' => 'Please enter a password.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Passwords do not match.',
            ]);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                return back()->withErrors($validator)->withInput($request->only('email', 'name'))->with('error', $firstError);
            }

            try {
                // Create the user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role ?? 'jobseeker',
                ]);

                // Create corresponding profile based on role
                if ($user->role === 'employer') {
                    $user->employerProfile()->create([]);
                } else {
                    $user->jobSeekerProfile()->create([]);
                }

                // Log the user in
                Auth::login($user);

                // Redirect based on role
                if ($user->role === 'employer') {
                    return redirect()->route('employer.dashboard')->with('success', 'Your account has been created successfully!');
                } else {
                    return redirect()->route('account.dashboard')->with('success', 'Your account has been created successfully!');
                }

            } catch (\Exception $e) {
                \Log::error('Registration failed: ' . $e->getMessage());
                return back()->withInput($request->only('email', 'name'))->with('error', 'Registration failed. Please try again.');
            }

        } else {
            // Magic link registration
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users',
            ], [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already registered. Please sign in instead.',
            ]);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                return back()->withErrors($validator)->withInput()->with('error', $firstError);
            }

            try {
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
                    'role' => $request->role ?? 'jobseeker',
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Generate the login URL
                $loginUrl = route('auth.verify-token', ['token' => $token]);

                // Send the email
                \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\LoginCodeMail($loginUrl, 15));

                return redirect()->route('home')->with('success', 'Check your email! We sent you a sign-in link.');

            } catch (\Exception $e) {
                \Log::error('Magic link registration failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Failed to send sign-in link. Please try again.');
            }
        }
    }
} 