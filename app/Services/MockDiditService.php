<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MockDiditService extends DiditService
{
    /**
     * Create a mock verification session
     */
    public function createSession(array $payload = []): array
    {
        Log::info('Creating mock Didit session', ['payload' => $payload]);
        
        // Generate a random session ID
        $sessionId = Str::uuid()->toString();
        
        // Create a mock response
        return [
            'session_id' => $sessionId,
            'url' => route('kyc.success', ['session_id' => $sessionId, 'status' => 'completed']),
            'status' => 'created',
            'created_at' => now()->toIso8601String(),
        ];
    }
    
    /**
     * Get mock session status
     */
    public function getSessionStatus(string $sessionId): array
    {
        Log::info('Getting mock session status', ['session_id' => $sessionId]);
        
        return [
            'session_id' => $sessionId,
            'status' => 'completed',
            'completed_at' => now()->toIso8601String(),
        ];
    }
    
    /**
     * Mock signature verification
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        return true;
    }
    
    /**
     * Process mock webhook event
     */
    public function processWebhookEvent(array $event): void
    {
        Log::info('Processing mock webhook event', $event);
        
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
            }
        }
    }
}