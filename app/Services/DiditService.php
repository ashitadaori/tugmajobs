<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiditService
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
                $user->update([
                    'kyc_status' => 'verified',
                    'kyc_verified_at' => now(),
                    'kyc_data' => $event
                ]);
                
                Log::info('User KYC status updated to verified', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
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

