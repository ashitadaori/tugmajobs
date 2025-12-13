<?php

namespace App\Services;

use App\Contracts\KycServiceInterface;
use Illuminate\Support\Facades\Log;

class MockDiditService implements KycServiceInterface
{
    public function createSession(array $payload = []): array
    {
        Log::info('MockDiditService: Creating mock session', $payload);
        
        // Return a mock response that matches the real Didit API structure
        return [
            'session_id' => 'mock-session-' . time(),
            'session_number' => 'MOCK-' . strtoupper(substr(md5(time()), 0, 8)),
            'session_token' => 'mock.jwt.token.here',
            'url' => env('APP_URL', 'http://localhost') . '/kyc/mock-verify',
            'vendor_data' => $payload['vendor_data'] ?? 'mock-vendor-data',
            'metadata' => $payload['metadata'] ?? [],
            'status' => 'created',
            'callback' => $payload['callback'] ?? null,
            'workflow_id' => 'mock-workflow-id'
        ];
    }
    
    public function getSessionStatus(string $sessionId): array
    {
        Log::info('MockDiditService: Getting mock session status', ['session_id' => $sessionId]);
        
        return [
            'session_id' => $sessionId,
            'status' => 'completed',
            'vendor_data' => 'mock-vendor-data',
            'metadata' => [],
            'completed_at' => now()->toISOString()
        ];
    }
    
    public function verifySignature(string $payload, string $signature): bool
    {
        Log::info('MockDiditService: Mock signature verification (always returns true)');
        return true;
    }
    
    public function processWebhookEvent(array $event): void
    {
        Log::info('MockDiditService: Processing mock webhook event', $event);
        
        // In mock mode, immediately update user status
        $eventType = $event['event_type'] ?? 'session.completed';
        $sessionId = $event['session_id'] ?? null;
        $vendorData = $event['vendor_data'] ?? null;
        
        if ($sessionId && $vendorData) {
            $user = $this->getUserFromVendorData($vendorData);
            if ($user) {
                $user->update([
                    'kyc_status' => 'verified',
                    'kyc_verified_at' => now(),
                    'kyc_data' => array_merge($event, ['mock' => true])
                ]);
                
                Log::info('MockDiditService: User KYC status updated to verified', [
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
