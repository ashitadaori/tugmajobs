<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\DiditService;
use App\Contracts\KycServiceInterface;

class KycController extends Controller
{
    protected $didit;

    public function __construct(KycServiceInterface $didit)
    {
        $this->didit = $didit;
        $this->middleware('auth')->except(['webhook', 'redirectHandler', 'failure', 'handleUserRedirect']);
    }

    public function startVerification(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Load employer profile if user is an employer
            if ($user->isEmployer()) {
                $user->load('employerProfile');
            }
            
            // Log verification attempt with more details
            Log::info('KYC verification started', [
                'user_id' => $user->id,
                'user_type' => $user->role,
                'is_ajax' => $request->expectsJson(),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'user_agent' => $request->header('User-Agent'),
                'ip' => $request->ip()
            ]);
            
            // Check if user can start verification
            if (!$user->canStartKycVerification()) {
                Log::warning('User cannot start verification', [
                    'user_id' => $user->id,
                    'kyc_status' => $user->kyc_status,
                    'updated_at' => $user->updated_at
                ]);
                
                // Provide a more detailed error message based on the current status
                $errorMessage = 'You cannot start verification at this time.';
                
                if ($user->kyc_status === 'in_progress') {
                    $errorMessage = 'You have a verification in progress. If this is stuck, please wait a few minutes and try again, or contact support.';
                } elseif ($user->kyc_status === 'verified') {
                    $errorMessage = 'Your identity has already been verified. No further verification is needed.';
                }
                
                // Return JSON for AJAX requests or requests expecting JSON
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => $errorMessage,
                        'kyc_status' => $user->kyc_status,
                        'can_retry_at' => $user->kyc_status === 'in_progress' ? $user->updated_at->addMinutes(30)->toISOString() : null
                    ], 400);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }
            

            
            // Determine the name to use for the KYC verification
            $kycName = $user->name; // Default to user's name
            if ($user->isEmployer()) {
                if ($user->employerProfile && $user->employerProfile->company_name) {
                    $kycName = $user->employerProfile->company_name;
                } else {
                    // Try alternative relationship
                    $employer = $user->employer;
                    if ($employer && $employer->company_name) {
                        $kycName = $employer->company_name;
                    }
                }
            }
            
            Log::info('KYC name determined', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
                'kyc_name' => $kycName,
                'is_employer' => $user->isEmployer(),
                'has_employer_profile' => $user->employerProfile !== null,
                'employer_profile_company_name' => $user->employerProfile ? $user->employerProfile->company_name : null,
                'has_employer' => $user->employer !== null,
                'employer_company_name' => $user->employer ? $user->employer->company_name : null
            ]);

            $sessionData = [
                'vendor_data' => 'user-' . $user->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => $user->role, // jobseeker or employer
                    'account_id' => $user->id,
                    'name' => $kycName,
                    'email' => $user->email,
                ],
                'contact_details' => [
                    'email' => $user->email,
                    'email_lang' => 'en',
                ]
            ];

            // Add phone if available
            if (!empty($user->mobile)) {
                $sessionData['contact_details']['phone'] = $user->mobile;
            }



            // Check internet connectivity and create session
            try {
                // Try to connect directly to the Didit API
                $response = $this->didit->createSession($sessionData);
            } catch (\Exception $connectionError) {
                Log::error('Network connectivity issue', [
                    'error' => $connectionError->getMessage(),
                    'trace' => $connectionError->getTraceAsString()
                ]);
                
                $errorMessage = 'Network connectivity issue. Please check your internet connection and try again.';
                
                // Return JSON for AJAX requests or requests expecting JSON
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $errorMessage], 500);
                }
                
                // Return a more helpful error message
                return redirect()->route('kyc.failure')->with('error', $errorMessage);
            }
            
            Log::info('Didit session created successfully', [
                'session_id' => $response['session_id'] ?? null
            ]);
            
            // Update user status to in_progress and store session ID
            $user->update([
                'kyc_status' => 'in_progress',
                'kyc_session_id' => $response['session_id'] ?? null,
            ]);
            


            // Check if we have a valid URL from Didit
            $redirectUrl = $response['url'] ?? null;
            
            if (!$redirectUrl) {
                Log::error('No redirect URL received from Didit', [
                    'response' => $response,
                    'user_id' => $user->id
                ]);
                
                $errorMessage = 'Failed to get verification URL from Didit. Please try again.';
                
                // Return JSON for AJAX requests or requests expecting JSON
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $errorMessage], 500);
                }
                
                return redirect()->route('kyc.failure')->with('error', $errorMessage);
            }
            
            // Validate that the URL is a Didit URL
            if (!str_contains($redirectUrl, 'didit.me') && !str_contains($redirectUrl, 'verification.didit.me')) {
                Log::warning('Received non-Didit URL', [
                    'url' => $redirectUrl,
                    'user_id' => $user->id
                ]);
            }
            
            Log::info('KYC verification URL generated successfully', [
                'user_id' => $user->id,
                'session_id' => $response['session_id'] ?? null,
                'url_host' => parse_url($redirectUrl, PHP_URL_HOST)
            ]);

            // Return JSON for AJAX requests or requests expecting JSON
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'url' => $redirectUrl,
                    'session_id' => $response['session_id'] ?? null
                ]);
            }

            return redirect($redirectUrl);
            
        } catch (\Exception $e) {
            Log::error('Failed to start KYC verification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => [
                    'method' => $request->method(),
                    'headers' => $request->headers->all(),
                    'is_ajax' => $request->ajax(),
                    'expects_json' => $request->expectsJson(),
                    'wants_json' => $request->wantsJson()
                ]
            ]);
            
            $errorMessage = 'Failed to start verification: ' . $e->getMessage();
            
            // Return JSON for AJAX requests or requests expecting JSON
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $errorMessage], 500);
            }
            
            // For development, show the error directly
            return redirect()->route('kyc.failure')->with('error', $errorMessage);
        }
    }

    // Webhook method moved to KycWebhookController for better separation of concerns

    public function redirectHandler(Request $request)
    {
        $sessionId = $request->query('session_id');
        $status = $request->query('status');
        
        Log::info('KYC redirect handler', [
            'session_id' => $sessionId,
            'status' => $status,
            'user_id' => Auth::id(),
            'is_authenticated' => Auth::check(),
            'all_params' => $request->all()
        ]);

        // Validate that we have required parameters from Didit
        if (!$sessionId || !$status) {
            Log::warning('KYC redirect accessed without proper parameters', [
                'session_id' => $sessionId,
                'status' => $status,
                'user_id' => Auth::id()
            ]);
            
            // Redirect to KYC start page if accessed directly
            if (Auth::check()) {
                return redirect()->route('kyc.start.form')->with('error', 'Invalid verification link. Please start the verification process.');
            } else {
                return redirect()->route('login')->with('error', 'Please login to access KYC verification.');
            }
        }

        // If user is not authenticated, try to find the user by session ID
        if (!Auth::check() && $sessionId) {
            $user = \App\Models\User::where('kyc_session_id', $sessionId)->first();
            if ($user) {
                // Log the user in automatically
                Auth::login($user);
                Log::info('User auto-logged in from KYC redirect', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
            } else {
                Log::warning('No user found for session ID', ['session_id' => $sessionId]);
                return redirect()->route('login')->with('error', 'Session not found. Please login and try again.');
            }
        }
        
        // If still not authenticated, redirect to login
        if (!Auth::check()) {
            Log::warning('User not authenticated after KYC redirect', ['session_id' => $sessionId]);
            return redirect()->route('login')->with('error', 'Please login to complete your verification.');
        }

        // Validate session ID matches current user
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->kyc_session_id !== $sessionId) {
                Log::warning('Session ID mismatch', [
                    'user_id' => $user->id,
                    'user_session_id' => $user->kyc_session_id,
                    'provided_session_id' => $sessionId
                ]);
                return redirect()->route('kyc.start.form')->with('error', 'Invalid session. Please start verification again.');
            }

            // Update user status based on the redirect status
            switch ($status) {
                case 'completed':
                case 'approved':  // Add Didit's 'approved' status
                    $user->update([
                        'kyc_status' => 'verified',
                        'kyc_verified_at' => now(),
                    ]);
                    break;
                case 'failed':
                    $user->update(['kyc_status' => 'failed']);
                    break;
                case 'expired':
                    $user->update(['kyc_status' => 'expired']);
                    break;
            }
            
            Log::info('User KYC status updated from redirect', [
                'user_id' => $user->id,
                'new_status' => $user->kyc_status,
                'session_id' => $sessionId
            ]);
        }

        // Return appropriate response based on status
        if ($status === 'completed' || $status === 'approved') {
            // Check if this is a mobile device (likely from QR code scan)
            $userAgent = $request->header('User-Agent');
            $isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i', $userAgent);
            
            Log::info('KYC completion redirect', [
                'session_id' => $sessionId,
                'status' => $status,
                'is_mobile' => $isMobile,
                'user_agent' => $userAgent,
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
                'request_url' => $request->fullUrl()
            ]);
            
            // If this is a mobile device, show mobile success page
            if ($isMobile) {
                return view('kyc.mobile-success', [
                    'sessionId' => $sessionId,
                    'userId' => Auth::id(),
                    'message' => 'Verification completed on mobile device. Return to your computer browser.'
                ]);
            }
            
            // Desktop redirect logic
            if (Auth::check()) {
                $user = Auth::user();
                $isEmployer = $user->isEmployer();

                // Stay on documents page for employers, go to dashboard for jobseekers
                $redirectRoute = $isEmployer ? 'employer.documents.index' : 'account.dashboard';

                Log::info('Desktop KYC completion redirect', [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'is_employer' => $isEmployer,
                    'redirect_route' => $redirectRoute,
                    'session_id' => session()->getId()
                ]);

                // Ensure the route exists before redirecting
                try {
                    $redirectUrl = route($redirectRoute);
                    Log::info('Redirect URL generated successfully', [
                        'route' => $redirectRoute,
                        'url' => $redirectUrl
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate redirect URL', [
                        'route' => $redirectRoute,
                        'error' => $e->getMessage()
                    ]);
                    // Fallback to home page if route doesn't exist
                    $redirectRoute = 'home';
                }

                return redirect()->route($redirectRoute)->with('success',
                    'Identity verification completed successfully! Your account is now verified.');
            } else {
                return view('kyc.success', compact('sessionId'));
            }
        } elseif (in_array($status, ['failed', 'error', 'expired'])) {
            // For failed/error/expired status, redirect back to KYC start page
            $errorMessage = 'Verification failed. Please try again with clear documents and good lighting.';
            if ($status === 'expired') {
                $errorMessage = 'Verification session expired. Please start a new verification.';
            }
            
            return redirect()->route('kyc.start.form')->with('error', $errorMessage);
        } else {
            return view('kyc.pending', compact('sessionId'));
        }
    }

    public function checkStatus(Request $request)
    {
        try {
            Log::info('KYC status check requested', [
                'user_id' => $request->input('user_id'),
                'session_id' => $request->input('session_id'),
                'auth_user_id' => Auth::id(),
                'request_method' => $request->method(),
                'is_ajax' => $request->ajax()
            ]);
            
            // Check if user_id is provided (for polling from frontend)
            $userId = $request->input('user_id');
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $statusData = [
                        'kyc_status' => $user->kyc_status,
                        'kyc_verified_at' => $user->kyc_verified_at,
                        'kyc_session_id' => $user->kyc_session_id,
                        'is_verified' => $user->isKycVerified(),
                        'needs_verification' => $user->needsKycVerification(),
                        'can_start_verification' => $user->canStartKycVerification(),
                        'status_text' => $user->kyc_status_text ?? ucfirst($user->kyc_status),
                        'updated_at' => $user->updated_at
                    ];
                    
                    Log::info('KYC status check response', [
                        'user_id' => $userId,
                        'status_data' => $statusData
                    ]);
                    
                    return response()->json($statusData);
                } else {
                    Log::warning('User not found for status check', ['user_id' => $userId]);
                    return response()->json(['error' => 'User not found'], 404);
                }
            }
            
            // Legacy session ID check
            $sessionId = $request->input('session_id');
            
            if (!$sessionId) {
                Log::warning('No session ID or user ID provided for status check');
                return response()->json(['error' => 'Session ID or User ID required'], 400);
            }

            $status = $this->didit->getSessionStatus($sessionId);
            
            Log::info('Didit session status retrieved', [
                'session_id' => $sessionId,
                'status' => $status
            ]);
            
            return response()->json($status);
            
        } catch (\Exception $e) {
            Log::error('Failed to check KYC status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $request->input('session_id'),
                'user_id' => $request->input('user_id'),
                'auth_user_id' => Auth::id()
            ]);
            
            return response()->json(['error' => 'Failed to check status: ' . $e->getMessage()], 500);
        }
    }

    public function failure()
    {
        return view('kyc.failure');
    }

    public function resetVerification(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('KYC reset requested', [
                'user_id' => $user->id,
                'current_status' => $user->kyc_status,
                'is_ajax' => $request->expectsJson()
            ]);
            
            // Only allow reset for certain statuses
            $allowedStatuses = ['in_progress', 'failed', 'expired'];
            
            if (!in_array($user->kyc_status, $allowedStatuses)) {
                $errorMessage = 'Cannot reset KYC verification in current status: ' . $user->kyc_status;
                
                Log::warning('KYC reset not allowed', [
                    'user_id' => $user->id,
                    'current_status' => $user->kyc_status,
                    'allowed_statuses' => $allowedStatuses
                ]);
                
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $errorMessage], 400);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }
            
            // Reset the KYC status
            $user->update([
                'kyc_status' => 'pending',
                'kyc_session_id' => null,
                'kyc_data' => null,
                'kyc_verified_at' => null
            ]);
            
            Log::info('KYC status reset successfully', [
                'user_id' => $user->id,
                'previous_status' => $user->getOriginal('kyc_status'),
                'new_status' => 'pending'
            ]);
            
            $successMessage = 'KYC verification has been reset. You can now start a new verification process.';
            
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'kyc_status' => 'pending'
                ]);
            }
            
            return redirect()->route('kyc.start.form')->with('success', $successMessage);
            
        } catch (\Exception $e) {
            Log::error('Failed to reset KYC verification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            $errorMessage = 'Failed to reset verification: ' . $e->getMessage();
            
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $errorMessage], 500);
            }
            
            return redirect()->back()->with('error', $errorMessage);
        }
    }
    
    public function handleUserRedirect(Request $request)
    {
        // This handles GET requests to the webhook URL, which are user redirects from Didit
        Log::info('KYC user redirect from Didit via webhook URL', [
            'all_params' => $request->all(),
            'query_params' => $request->query(),
            'headers' => $request->headers->all(),
            'user_id' => Auth::id(),
            'is_authenticated' => Auth::check(),
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);
        
        // Extract parameters that Didit might send
        $sessionId = $request->query('session_id') ?? $request->query('sessionId');
        $status = $request->query('status') ?? $request->query('verification_status') ?? 'completed';
        $error = $request->query('error') ?? $request->query('error_message');
        
        // If no session ID in URL, try to find current user's active session
        if (!$sessionId && Auth::check()) {
            $user = Auth::user();
            if ($user->kyc_session_id && $user->kyc_status === 'in_progress') {
                $sessionId = $user->kyc_session_id;
                Log::info('Using current user session ID for webhook redirect', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
            }
        }
        
        // Handle error cases - redirect back to KYC start page
        if ($error || in_array($status, ['failed', 'error', 'cancelled', 'expired'])) {
            Log::warning('KYC verification error or failure', [
                'status' => $status,
                'error' => $error,
                'user_id' => Auth::id()
            ]);
            
            if (Auth::check()) {
                $user = Auth::user();
                
                // Update user status based on the error type
                $kycStatus = 'failed';
                if ($status === 'expired') {
                    $kycStatus = 'expired';
                } elseif ($status === 'cancelled') {
                    $kycStatus = 'pending'; // Allow them to try again
                }
                
                $user->update([
                    'kyc_status' => $kycStatus,
                    'kyc_data' => [
                        'session_id' => $sessionId ?? 'error-' . time(),
                        'status' => $status,
                        'error' => $error,
                        'failed_at' => now()->toIso8601String(),
                        'redirect_failure' => true
                    ]
                ]);
                
                Log::info('User KYC status updated from error redirect', [
                    'user_id' => $user->id,
                    'new_status' => $kycStatus,
                    'error' => $error
                ]);
            }
            
            // Redirect back to KYC start page with error message
            $errorMessage = 'Verification failed. Please try again.';
            if ($error) {
                $errorMessage = 'Verification error: ' . $error;
            } elseif ($status === 'expired') {
                $errorMessage = 'Verification session expired. Please start a new verification.';
            } elseif ($status === 'cancelled') {
                $errorMessage = 'Verification was cancelled. You can try again when ready.';
            }
            
            return redirect()->route('kyc.start.form')->with('error', $errorMessage);
        }
        
        // If we have session info for successful verification, redirect to the success handler
        if ($sessionId && in_array($status, ['completed', 'success', 'verified'])) {
            return redirect()->route('kyc.success', [
                'session_id' => $sessionId,
                'status' => $status
            ]);
        }
        
        // Find user either by authentication or session ID
        $user = null;
        
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('User authenticated for webhook redirect', ['user_id' => $user->id]);
        } elseif ($sessionId) {
            $user = \App\Models\User::where('kyc_session_id', $sessionId)->first();
            if ($user) {
                Auth::login($user);
                Log::info('User auto-logged in from handleUserRedirect', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
            }
        }
        
        // If we still don't have a user and there's no session ID, try the current auth user's session
        if (!$user && Auth::check()) {
            $user = Auth::user();
            if ($user->kyc_session_id && $user->kyc_status === 'in_progress') {
                $sessionId = $user->kyc_session_id;
                Log::info('Using authenticated user session for redirect', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
            }
        }
        
        // If we still don't have a user, return an error
        if (!$user) {
            Log::warning('No user found for webhook redirect', [
                'session_id' => $sessionId,
                'status' => $status,
                'auth_check' => Auth::check()
            ]);
            return redirect()->route('login')->with('info', 'Please login to complete your verification.');
        }
        
        // If no session info but user is authenticated, assume successful completion
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update user status to verified (assuming successful completion if they're redirected here)
            $user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
                'kyc_data' => [
                    'session_id' => 'redirect-' . time(),
                    'status' => 'completed',
                    'completed_at' => now()->toIso8601String(),
                    'redirect_completion' => true
                ]
            ]);
            
            Log::info('User KYC status updated from redirect', [
                'user_id' => $user->id,
                'new_status' => 'verified'
            ]);
            
            // Redirect to documents page for employers, dashboard for jobseekers
            $isEmployer = $user->isEmployer();
            $redirectRoute = $isEmployer ? 'employer.documents.index' : 'account.dashboard';

            Log::info('KYC handleUserRedirect completion', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'is_employer' => $isEmployer,
                'redirect_route' => $redirectRoute,
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
                'session_id' => session()->getId(),
                'request_url' => $request->fullUrl()
            ]);

            // Don't regenerate session to avoid potential issues
            // session()->regenerate();

            // Ensure the route exists before redirecting
            try {
                $redirectUrl = route($redirectRoute);
                Log::info('Redirect URL generated successfully in handleUserRedirect', [
                    'route' => $redirectRoute,
                    'url' => $redirectUrl
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to generate redirect URL in handleUserRedirect', [
                    'route' => $redirectRoute,
                    'error' => $e->getMessage()
                ]);
                // Fallback to home page if route doesn't exist
                $redirectRoute = 'home';
            }

            return redirect()->route($redirectRoute)->with('success',
                'Identity verification completed successfully! Your account is now verified.');
        }
        
        // Fallback: redirect to login
        return redirect()->route('login')->with('info', 'Please login to complete your verification.');
    }
    
    public function mobileCompletionNotify(Request $request)
    {
        try {
            $sessionId = $request->input('session_id');
            $userId = $request->input('user_id');
            
            Log::info('Mobile completion notification received', [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'user_agent' => $request->header('User-Agent'),
                'ip' => $request->ip(),
                'timestamp' => $request->input('timestamp')
            ]);
            
            // Find the user and update their status if needed
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user && $user->kyc_status === 'in_progress') {
                    $user->update([
                        'kyc_status' => 'verified',
                        'kyc_verified_at' => now(),
                        'kyc_data' => array_merge($user->kyc_data ?? [], [
                            'mobile_completion' => true,
                            'mobile_notification_received' => now()->toIso8601String(),
                            'mobile_user_agent' => $request->header('User-Agent')
                        ])
                    ]);
                    
                    Log::info('User KYC status updated from mobile notification', [
                        'user_id' => $user->id,
                        'new_status' => 'verified'
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Mobile completion notification received'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to process mobile completion notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to process notification'
            ], 500);
        }
    }

}
