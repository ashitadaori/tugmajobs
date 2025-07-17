<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PersonaKYCService
{
    protected $apiKey;
    protected $templateId;
    protected $baseUrl;
    protected $environment;

    public function __construct()
    {
        $this->apiKey = config('services.persona.api_key');
        $this->templateId = config('services.persona.template_id');
        $this->environment = config('app.env') === 'production' ? 'production' : 'sandbox';
        $this->baseUrl = 'https://withpersona.com/api/v1';
    }

    public function createInquiry(User $user)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Persona-Version' => '2023-01-01'
            ])->post($this->baseUrl . '/inquiries', [
                'template_id' => $this->templateId,
                'reference_id' => (string) $user->id,
                'fields' => [
                    'name_first' => $user->name,
                    'email_address' => $user->email
                ],
                'environment' => $this->environment
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'inquiry_id' => $data['data']['id'],
                    'verification_url' => $data['data']['attributes']['url']
                ];
            }

            Log::error('Persona API Error: ' . $response->body());
            return [
                'success' => false,
                'error' => 'Failed to create inquiry: ' . ($response->json()['message'] ?? 'Unknown error')
            ];
        } catch (\Exception $e) {
            Log::error('Persona API Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Service unavailable'
            ];
        }
    }

    public function getInquiryStatus($inquiryId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Persona-Version' => '2023-01-01'
            ])->get($this->baseUrl . '/inquiries/' . $inquiryId);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['data']['attributes']['status'];
                
                // Map Persona status to our status
                $statusMap = [
                    'completed' => 'completed',
                    'failed' => 'failed',
                    'expired' => 'failed',
                    'pending' => 'in_progress',
                    'reviewing' => 'in_progress'
                ];

                $mappedStatus = $statusMap[$status] ?? 'in_progress';
                
                // Update user status if completed or failed
                $user = User::where('kyc_inquiry_id', $inquiryId)->first();
                if ($user) {
                    if ($mappedStatus === 'completed') {
                        if ($user->role === 'jobseeker') {
                            $user->jobSeekerProfile()->update(['is_kyc_verified' => true]);
                        } elseif ($user->role === 'employer') {
                            $user->employerProfile()->update(['is_verified' => true]);
                        }
                        $user->update([
                            'kyc_status' => 'verified',
                            'kyc_completed_at' => now()
                        ]);
                    } elseif ($mappedStatus === 'failed') {
                        $user->update([
                            'kyc_status' => 'failed',
                            'kyc_completed_at' => now()
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'status' => $mappedStatus,
                    'completed_at' => $data['data']['attributes']['completed_at'] ?? null,
                    'verification_url' => $data['data']['attributes']['url'] ?? null
                ];
            }

            Log::error('Persona API Error: ' . $response->body());
            return [
                'success' => false,
                'error' => 'Failed to get inquiry status: ' . ($response->json()['message'] ?? 'Unknown error')
            ];
        } catch (\Exception $e) {
            Log::error('Persona API Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Service unavailable'
            ];
        }
    }

    // This method is only used in production mode
    public function handleWebhook($payload, $signature)
    {
        // In sandbox mode, we don't process webhooks
        if ($this->environment === 'sandbox') {
            Log::info('Webhook received in sandbox mode - ignoring');
            return true;
        }

        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                Log::error('Invalid webhook signature');
                return false;
            }

            $event = json_decode($payload, true);
            
            // Validate event data
            if (!isset($event['data']['id']) || !isset($event['data']['attributes']['status'])) {
                Log::error('Invalid webhook payload structure');
                return false;
            }

            $inquiryId = $event['data']['id'];
            $status = $event['data']['attributes']['status'];

            // Handle the webhook event
            if ($status === 'completed') {
                $user = User::where('kyc_inquiry_id', $inquiryId)->first();
                if ($user) {
                    if ($user->role === 'jobseeker') {
                        $user->jobSeekerProfile()->update(['is_kyc_verified' => true]);
                    } elseif ($user->role === 'employer') {
                        $user->employerProfile()->update(['is_verified' => true]);
                    }
                    $user->update([
                        'kyc_status' => 'verified',
                        'kyc_completed_at' => now()
                    ]);
                }
            } elseif (in_array($status, ['failed', 'expired'])) {
                $user = User::where('kyc_inquiry_id', $inquiryId)->first();
                if ($user) {
                    $user->update([
                        'kyc_status' => 'failed',
                        'kyc_completed_at' => now()
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Webhook handling error: ' . $e->getMessage());
            return false;
        }
    }

    protected function verifyWebhookSignature($payload, $signature)
    {
        // In sandbox mode, we don't verify webhooks
        if ($this->environment === 'sandbox') {
            return true;
        }

        $webhookSecret = config('services.persona.webhook_secret');
        
        if (empty($webhookSecret)) {
            Log::error('Webhook secret not configured');
            return false;
        }

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        return hash_equals($computedSignature, $signature);
    }
} 