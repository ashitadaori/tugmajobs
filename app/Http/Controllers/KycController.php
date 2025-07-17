<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\DiditService;

class KycController extends Controller
{
    protected $didit;

    public function __construct(DiditService $didit)
    {
        $this->didit = $didit;
        $this->middleware('auth')->except(['webhook', 'redirectHandler', 'failure']);
    }

    public function startVerification(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Debug information
            Log::info('KYC verification attempt', [
                'user_id' => $user->id,
                'user_type' => $user->role,
                'kyc_status' => $user->kyc_status
            ]);
            
            // Check if user can start verification
            if (!$user->canStartKycVerification()) {
                Log::warning('User cannot start verification', [
                    'user_id' => $user->id,
                    'kyc_status' => $user->kyc_status
                ]);
                return redirect()->back()->with('error', 'You cannot start verification at this time.');
            }
            
            // Debug Didit configuration
            Log::info('Didit configuration', [
                'auth_url' => config('services.didit.auth_url'),
                'base_url' => config('services.didit.base_url'),
                'api_key_set' => !empty(config('services.didit.api_key')),
                'client_id_set' => !empty(config('services.didit.client_id')),
                'client_secret_set' => !empty(config('services.didit.client_secret')),
                'workflow_id_set' => !empty(config('services.didit.workflow_id')),
                'callback_url' => config('services.didit.callback_url'),
                'redirect_url' => config('services.didit.redirect_url')
            ]);
            
            $sessionData = [
                'vendor_data' => 'user-' . $user->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => $user->role, // jobseeker or employer
                    'account_id' => $user->id,
                    'name' => $user->name,
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

            Log::info('Creating Didit session with data', [
                'session_data' => $sessionData
            ]);

            // Check internet connectivity
            try {
                // Try to connect directly to the Didit API
                $response = $this->didit->createSession($sessionData);
            } catch (\Exception $connectionError) {
                Log::error('Network connectivity issue', [
                    'error' => $connectionError->getMessage()
                ]);
                
                // Return a more helpful error message
                return redirect()->route('kyc.failure')->with('error', 
                    'Network connectivity issue. Please check your internet connection and try again.');
            }
            
            Log::info('Didit session created', [
                'response' => $response
            ]);
            
            // Update user status to in_progress and store session ID
            $user->update([
                'kyc_status' => 'in_progress',
                'kyc_session_id' => $response['session_id'] ?? null,
            ]);
            
            Log::info('KYC verification started', [
                'user_id' => $user->id,
                'user_type' => $user->role,
                'session_id' => $response['session_id'] ?? null,
                'redirect_url' => $response['url'] ?? route('kyc.failure')
            ]);

            return redirect($response['url'] ?? route('kyc.failure'));
            
        } catch (\Exception $e) {
            Log::error('Failed to start KYC verification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            // For development, show the error directly
            return redirect()->route('kyc.failure')->with('error', 'Failed to start verification: ' . $e->getMessage());
        }
    }

    public function webhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('x-signature');

            if (!$this->didit->verifySignature($payload, $signature)) {
                Log::warning('Invalid Didit webhook signature', [
                    'signature' => $signature,
                    'payload_length' => strlen($payload)
                ]);
                return response('Invalid signature', 400);
            }

            $event = json_decode($payload, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in webhook payload', ['error' => json_last_error_msg()]);
                return response('Invalid JSON', 400);
            }

            Log::info('Valid Didit webhook received', $event);
            
            // Process the webhook event
            $this->didit->processWebhookEvent($event);

            return response()->json(['status' => 'received']);
            
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
            return response('Internal server error', 500);
        }
    }

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
            }
        }

        // Update user status based on the redirect status
        if (Auth::check() && $sessionId) {
            $user = Auth::user();
            if ($user->kyc_session_id === $sessionId) {
                switch ($status) {
                    case 'completed':
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
        }

        if ($status === 'completed') {
            return view('kyc.success', compact('sessionId'));
        } elseif ($status === 'failed') {
            return view('kyc.failure', compact('sessionId'));
        } else {
            return view('kyc.pending', compact('sessionId'));
        }
    }

    public function checkStatus(Request $request)
    {
        try {
            $sessionId = $request->input('session_id');
            
            if (!$sessionId) {
                return response()->json(['error' => 'Session ID required'], 400);
            }

            $status = $this->didit->getSessionStatus($sessionId);
            
            return response()->json($status);
            
        } catch (\Exception $e) {
            Log::error('Failed to check KYC status', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id')
            ]);
            
            return response()->json(['error' => 'Failed to check status'], 500);
        }
    }

    public function failure()
    {
        return view('kyc.failure');
    }
}
