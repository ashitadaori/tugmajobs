<?php

namespace App\Services;

use App\Contracts\KycServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiditService implements KycServiceInterface
{
    protected string $authUrl;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected string $clientSecret;
    protected string $workflowId;
    protected string $callbackUrl;
    protected string $redirectUrl;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->authUrl = config('services.didit.auth_url');
        $this->baseUrl = config('services.didit.base_url');
        $this->apiKey = config('services.didit.api_key');
        $this->clientId = config('services.didit.client_id');
        $this->clientSecret = config('services.didit.client_secret');
        $this->workflowId = config('services.didit.workflow_id');
        $this->callbackUrl = config('services.didit.callback_url');
        $this->redirectUrl = config('services.didit.redirect_url');
        $this->webhookSecret = config('services.didit.webhook_secret');
    }

    public function fetchAccessToken(): string
    {
        // For Didit API, we might not need OAuth2 token authentication
        // The API key might be sufficient for direct API calls
        // Let's skip token authentication for now since session creation works
        Log::info('Skipping token authentication - using API key directly');
        return 'api-key-auth';
    }

    public function createSession(array $payload = []): array
    {
        $defaultPayload = [
            'callback'        => $this->callbackUrl,  // This is for webhooks only
            'vendor_data'     => $payload['vendor_data'] ?? 'user-' . time(),
            'metadata'        => $payload['metadata'] ?? [],
            'contact_details' => $payload['contact_details'] ?? [],
        ];

        // Only add workflow_id if it's configured and not a placeholder
        if ($this->workflowId && !str_starts_with($this->workflowId, 'your_')) {
            $defaultPayload['workflow_id'] = $this->workflowId;
        }

        $sessionPayload = array_merge($defaultPayload, $payload);

        Log::info('Didit session request', [
            'url'     => $this->baseUrl . '/v2/session',
            'payload' => $sessionPayload,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Api-Key'    => $this->apiKey,
        ])->post($this->baseUrl . '/v2/session', $sessionPayload);

        Log::info('Didit session response', [
            'status' => $response->status(),
            'body'   => $response->body(),
            'headers' => $response->headers(),
        ]);

        if (!$response->successful()) {
            Log::error('Session creation failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('Failed to create verification session: ' . $response->body());
        }

        $jsonResponse = $response->json();
        
        // Debug: Log the actual response structure
        Log::info('DiditService returning response', [
            'json_response' => $jsonResponse,
            'response_keys' => array_keys($jsonResponse ?? [])
        ]);
        
        return $jsonResponse;
    }

    public function getSessionStatus(string $sessionId): array
    {
        Log::info('Getting Didit session status', ['session_id' => $sessionId]);

        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get($this->baseUrl . '/v2/session/' . $sessionId);

        if (!$response->successful()) {
            Log::error('Failed to get session status', [
                'session_id' => $sessionId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to get session status: ' . $response->body());
        }

        return $response->json();
    }

    public function getSessionDetails(string $sessionId): array
    {
        // This method is an alias for getSessionStatus but with better naming
        // It can also include additional processing if needed
        return $this->getSessionStatus($sessionId);
    }

    public function getDetailedVerificationData(string $sessionId): array
    {
        Log::info('Getting detailed Didit verification data', ['session_id' => $sessionId]);

        try {
            // Try different endpoints that might contain detailed data
            $endpoints = [
                '/v2/session/' . $sessionId,
                '/v2/session/' . $sessionId . '/details',
                '/v2/session/' . $sessionId . '/documents',
                '/v2/session/' . $sessionId . '/result'
            ];

            $allData = [];

            foreach ($endpoints as $endpoint) {
                try {
                    $response = Http::withHeaders([
                        'X-Api-Key' => $this->apiKey,
                    ])->get($this->baseUrl . $endpoint);

                    if ($response->successful()) {
                        $data = $response->json();
                        $allData[str_replace(['/', $sessionId], ['_', 'SESSION'], $endpoint)] = $data;
                        Log::info('Successfully fetched data from endpoint', [
                            'endpoint' => $endpoint,
                            'data_keys' => array_keys($data)
                        ]);
                    }
                } catch (Exception $e) {
                    Log::debug('Endpoint not available', [
                        'endpoint' => $endpoint,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return $allData;

        } catch (Exception $e) {
            Log::error('Failed to get detailed verification data', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function verifySignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('Webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    public function processWebhookEvent(array $event): void
    {
        Log::info('Processing Didit webhook event', $event);

        $eventType = $event['event_type'] ?? null;
        $sessionId = $event['session_id'] ?? null;

        switch ($eventType) {
            case 'session.completed':
                $this->handleSessionCompleted($event);
                break;
            case 'session.failed':
                $this->handleSessionFailed($event);
                break;
            case 'session.expired':
                $this->handleSessionExpired($event);
                break;
            default:
                Log::info('Unhandled Didit event type', ['event_type' => $eventType]);
        }
    }

    protected function handleSessionCompleted(array $event): void
    {
        Log::info('KYC verification completed', $event);
        
        $sessionId = $event['session_id'] ?? null;
        $vendorData = $event['vendor_data'] ?? null;
        
        if ($sessionId && $vendorData) {
            $user = $this->getUserFromVendorData($vendorData);
            if ($user) {
                // Store the complete webhook event data
                $kycData = [
                    'webhook_event' => $event,
                    'session_id' => $sessionId,
                    'status' => 'completed',
                    'completed_at' => now()->toIso8601String(),
                    'webhook_received' => true,
                    'data_source' => 'didit_webhook'
                ];

                // Try to fetch additional detailed data
                try {
                    $detailedData = $this->getDetailedVerificationData($sessionId);
                    if (!empty($detailedData)) {
                        $kycData['detailed_verification_data'] = $detailedData;
                        Log::info('Enhanced webhook data with detailed verification info', [
                            'user_id' => $user->id,
                            'endpoints_fetched' => count($detailedData)
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not fetch detailed verification data in webhook', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Process verification data using KycVerificationService
                try {
                    $kycVerificationService = app(\App\Services\KycVerificationService::class);
                    
                    // Prepare data for the verification service
                    $verificationData = array_merge($event, [
                        'raw_data' => $event,
                        'verification_data' => $detailedData ?? [],
                        'status' => 'verified'
                    ]);
                    
                    $verification = $kycVerificationService->processVerificationData($verificationData);
                    
                    if ($verification) {
                        Log::info('KYC verification record created/updated', [
                            'verification_id' => $verification->id,
                            'user_id' => $user->id,
                            'session_id' => $sessionId
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process verification data with KycVerificationService', [
                        'user_id' => $user->id,
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                }

                $user->update([
                    'kyc_status' => 'verified',
                    'kyc_verified_at' => now(),
                    'kyc_data' => $kycData
                ]);
                
                // Create notification for successful verification
                $user->notifications()->create([
                    'title' => 'Identity Verification Completed',
                    'message' => 'Your identity has been successfully verified! You now have access to all platform features.',
                    'type' => 'kyc_verified',
                    'data' => [
                        'session_id' => $sessionId,
                        'verified_at' => now()->toIso8601String()
                    ],
                    'action_url' => $user->role === 'employer' ? route('employer.dashboard') : route('account.dashboard')
                ]);
                
                Log::info('User KYC status updated to verified with enhanced data', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'has_detailed_data' => isset($kycData['detailed_verification_data'])
                ]);
            }
        }
    }

    protected function handleSessionFailed(array $event): void
    {
        Log::warning('KYC verification failed', $event);
        
        $sessionId = $event['session_id'] ?? null;
        $vendorData = $event['vendor_data'] ?? null;
        
        if ($sessionId && $vendorData) {
            $user = $this->getUserFromVendorData($vendorData);
            if ($user) {
                $user->update([
                    'kyc_status' => 'failed',
                    'kyc_data' => $event
                ]);
                
                Log::info('User KYC status updated to failed', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
            }
        }
    }

    protected function handleSessionExpired(array $event): void
    {
        Log::info('KYC session expired', $event);
        
        $sessionId = $event['session_id'] ?? null;
        $vendorData = $event['vendor_data'] ?? null;
        
        if ($sessionId && $vendorData) {
            $user = $this->getUserFromVendorData($vendorData);
            if ($user) {
                $user->update([
                    'kyc_status' => 'expired',
                    'kyc_data' => $event
                ]);
                
                Log::info('User KYC status updated to expired', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
            }
        }
    }

    protected function getUserFromVendorData(string $vendorData): ?\App\Models\User
    {
        // Extract user ID from vendor_data (format: "user-{id}")
        if (preg_match('/user-(\d+)/', $vendorData, $matches)) {
            return \App\Models\User::find($matches[1]);
        }
        
        return null;
    }
}

