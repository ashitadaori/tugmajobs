<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\KycData;

class KycWebhookController extends Controller
{
    /**
     * Handle the incoming KYC webhook from Didit
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        Log::info('KYC Webhook received', [
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            // Step A: Verify Webhook Signature (Security)
            $signature = $request->header('X-Didit-Signature') ?? $request->header('x-signature') ?? $request->header('X-Signature');
            $rawPayload = $request->getContent();
            
            if (!$this->verifyWebhookSignature($rawPayload, $signature)) {
                Log::warning('Invalid webhook signature', [
                    'provided_signature' => $signature,
                    'payload_length' => strlen($rawPayload),
                    'ip' => $request->ip(),
                ]);
                
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            // Parse the JSON payload
            $payload = json_decode($rawPayload, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in webhook payload', [
                    'json_error' => json_last_error_msg(),
                    'payload_preview' => substr($rawPayload, 0, 200),
                ]);
                
                return response()->json(['error' => 'Invalid JSON payload'], 400);
            }

            Log::info('Webhook payload parsed successfully', [
                'payload' => $payload,
                'session_id' => $payload['session_id'] ?? null,
                'status' => $payload['status'] ?? null,
            ]);

            // Step B: Find the User
            $sessionId = $payload['session_id'] ?? null;
            
            if (!$sessionId) {
                Log::error('No session_id provided in webhook payload', ['payload' => $payload]);
                return response()->json(['error' => 'Missing session_id'], 400);
            }

            $user = User::where('kyc_session_id', $sessionId)->first();
            
            if (!$user) {
                Log::warning('No user found for session_id', [
                    'session_id' => $sessionId,
                    'payload' => $payload,
                ]);
                
                return response()->json(['error' => 'User not found'], 404);
            }

            Log::info('User found for webhook', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'current_kyc_status' => $user->kyc_status,
                'session_id' => $sessionId,
            ]);

            // Step C: Update the Database within a Transaction
            DB::transaction(function () use ($user, $payload, $sessionId) {
                $status = strtolower($payload['status'] ?? '');
                $previousStatus = $user->kyc_status;
                
                Log::info('Processing KYC status update', [
                    'user_id' => $user->id,
                    'previous_status' => $previousStatus,
                    'new_status' => $status,
                    'payload_status' => $payload['status'] ?? null,
                ]);

                // Map status to standardized values
                $mappedStatus = $this->mapKycStatus($status);
                
                // Update user's basic KYC status
                $user->kyc_status = $mappedStatus;
                if ($mappedStatus === 'verified') {
                    $user->kyc_verified_at = now();
                }
                $user->save();
                
                // Save detailed KYC data to the new kyc_data table ONLY if verified
                if ($mappedStatus === 'verified') {
                    $this->saveKycData($user, $payload, $sessionId, $mappedStatus);
                    Log::info('KYC data saved for verified user', [
                        'user_id' => $user->id,
                        'session_id' => $sessionId
                    ]);
                } else {
                    Log::info('KYC data NOT saved - verification not successful', [
                        'user_id' => $user->id,
                        'session_id' => $sessionId,
                        'status' => $mappedStatus
                    ]);
                }
                
                Log::info('User KYC data saved successfully', [
                    'user_id' => $user->id,
                    'final_status' => $user->kyc_status,
                    'verified_at' => $user->kyc_verified_at,
                ]);
            });

            // Step D: Dispatch Events (Best Practice)
            // TODO: Dispatch KycVerified or KycFailed events here
            // Example:
            // if ($user->kyc_status === 'verified') {
            //     event(new KycVerified($user, $payload));
            // } elseif ($user->kyc_status === 'failed') {
            //     event(new KycFailed($user, $payload));
            // }

            // Create a notification for the user
            if (class_exists(\App\Models\Notification::class)) {
                try {
                    $notificationTitle = '';
                    $notificationMessage = '';
                    $notificationType = 'info';

                    switch ($user->kyc_status) {
                        case 'verified':
                            $notificationTitle = 'Identity Verification Completed';
                            $notificationMessage = 'Your identity has been successfully verified. You now have full access to all platform features.';
                            $notificationType = 'success';
                            break;
                        case 'failed':
                            $notificationTitle = 'Identity Verification Failed';
                            $notificationMessage = 'Your identity verification was unsuccessful. Please try again with clear documents and good lighting.';
                            $notificationType = 'error';
                            break;
                        case 'expired':
                            $notificationTitle = 'Identity Verification Expired';
                            $notificationMessage = 'Your identity verification session has expired. Please start a new verification process.';
                            $notificationType = 'warning';
                            break;
                    }

                    if ($notificationTitle) {
                        \App\Models\Notification::create([
                            'user_id' => $user->id,
                            'title' => $notificationTitle,
                            'message' => $notificationMessage,
                            'type' => $notificationType,
                            'data' => [
                                'kyc_status' => $user->kyc_status,
                                'session_id' => $sessionId,
                                'source' => 'kyc_webhook',
                            ],
                        ]);

                        Log::info('KYC notification created', [
                            'user_id' => $user->id,
                            'notification_type' => $notificationType,
                            'kyc_status' => $user->kyc_status,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to create KYC notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Step E: Return a Success Response
            Log::info('KYC webhook processed successfully', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'final_status' => $user->kyc_status,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully',
                'user_id' => $user->id,
                'kyc_status' => $user->kyc_status,
            ], 200);

        } catch (\Exception $e) {
            Log::error('KYC webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload_preview' => isset($rawPayload) ? substr($rawPayload, 0, 200) : null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Verify the webhook signature using HMAC-SHA256
     *
     * @param string $payload
     * @param string|null $signature
     * @return bool
     */
    private function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            Log::warning('No signature provided in webhook request');
            return false;
        }

        $webhookSecret = config('services.didit.webhook_secret');
        
        if (!$webhookSecret) {
            Log::error('DIDIT_WEBHOOK_SECRET not configured');
            return false;
        }

        // Debug: Log payload details
        Log::info('Webhook signature verification debug', [
            'payload_length' => strlen($payload),
            'payload_preview' => substr($payload, 0, 100),
            'webhook_secret_length' => strlen($webhookSecret),
            'original_signature' => $signature,
        ]);

        // Calculate the expected signature
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        // Try different signature formats that Didit might use
        $signatureFormats = [
            $signature, // Original
            str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature, // Remove sha256= prefix
            str_starts_with($signature, 'sha256:') ? substr($signature, 7) : $signature, // Remove sha256: prefix
        ];
        
        $isValid = false;
        $matchedFormat = null;
        
        foreach ($signatureFormats as $format) {
            if (hash_equals($expectedSignature, $format)) {
                $isValid = true;
                $matchedFormat = $format;
                break;
            }
        }
        
        // Try with base64 encoded secret (some services use this)
        if (!$isValid) {
            $base64Secret = base64_encode($webhookSecret);
            $expectedSignatureBase64 = hash_hmac('sha256', $payload, $base64Secret);
            
            foreach ($signatureFormats as $format) {
                if (hash_equals($expectedSignatureBase64, $format)) {
                    $isValid = true;
                    $matchedFormat = $format;
                    Log::info('Webhook signature matched with base64 encoded secret');
                    break;
                }
            }
        }
        
        // For development/testing: Allow bypassing signature verification if configured
        $bypassSignature = env('DIDIT_WEBHOOK_BYPASS_SIGNATURE', false);
        if (!$isValid && $bypassSignature && env('APP_ENV') !== 'production') {
            Log::warning('Webhook signature verification bypassed for development');
            $isValid = true;
            $matchedFormat = 'bypassed';
        }
        
        Log::info('Webhook signature verification result', [
            'is_valid' => $isValid,
            'expected_signature' => $expectedSignature,
            'matched_format' => $matchedFormat,
            'provided_signature' => $signature,
            'attempted_formats' => $signatureFormats,
        ]);

        return $isValid;
    }

    /**
     * Map various status values from Didit to standardized KYC status
     *
     * @param string $status
     * @return string
     */
    private function mapKycStatus(string $status): string
    {
        switch (strtolower($status)) {
            case 'verified':
            case 'completed':
            case 'success':
            case 'approved':
                return 'verified';
            
            case 'failed':
            case 'rejected':
            case 'error':
                return 'failed';
            
            case 'expired':
                return 'expired';
                
            case 'in progress':
            case 'in_progress':
            case 'processing':
            case 'pending':
                return 'in_progress';
                
            case 'not started':
            case 'not_started':
            case 'created':
            case 'initiated':
                // Don't change status for these intermediate states
                // Return current status or keep as in_progress if already set
                Log::info('Received intermediate KYC status', ['status' => $status]);
                return 'in_progress';
            
            default:
                Log::warning('Unknown KYC status received', ['status' => $status]);
                // For unknown statuses, don't change to failed immediately
                // Let's keep as in_progress to avoid breaking the flow
                return 'in_progress';
        }
    }

    /**
     * Save detailed KYC data to the kyc_data table
     *
     * @param User $user
     * @param array $payload
     * @param string $sessionId
     * @param string $mappedStatus
     * @return void
     */
    private function saveKycData(User $user, array $payload, string $sessionId, string $mappedStatus): void
    {
        try {
            // First, try to find existing KYC data for this session to update it
            $kycData = KycData::where('user_id', $user->id)
                              ->where('session_id', $sessionId)
                              ->first();

            if (!$kycData) {
                // Create new KYC data record
                $kycData = new KycData();
                $kycData->user_id = $user->id;
                $kycData->session_id = $sessionId;
            }

            // Map basic status information
            $kycData->status = $mappedStatus;
            $kycData->didit_status = $payload['status'] ?? null;
            $kycData->verification_method = 'webhook';
            $kycData->raw_payload = $payload;
            
            // Set verification timestamps
            if ($mappedStatus === 'verified') {
                $kycData->verified_at = now();
            }
            
            // Extract data from the Didit payload structure
            $decision = $payload['decision'] ?? [];
            $idVerification = $decision['id_verification'] ?? [];
            $faceMatch = $decision['face_match'] ?? [];
            $liveness = $decision['liveness'] ?? [];
            $ipAnalysis = $decision['ip_analysis'] ?? [];
            
            // Extract personal information from ID verification
            if (!empty($idVerification)) {
                $kycData->first_name = $idVerification['first_name'] ?? null;
                $kycData->last_name = $idVerification['last_name'] ?? null;
                $kycData->full_name = $idVerification['full_name'] ?? null;
                $kycData->date_of_birth = $this->parseDate($idVerification['date_of_birth'] ?? null);
                $kycData->gender = $idVerification['gender'] ?? null;
                $kycData->nationality = $idVerification['nationality'] ?? null;
                $kycData->place_of_birth = $idVerification['place_of_birth'] ?? null;
                $kycData->marital_status = $idVerification['marital_status'] ?? null;
            }
            
            // Extract document information
            if (!empty($idVerification)) {
                $kycData->document_type = $idVerification['document_type'] ?? null;
                $kycData->document_number = $idVerification['document_number'] ?? null;
                $kycData->document_issue_date = $this->parseDate($idVerification['date_of_issue'] ?? null);
                $kycData->document_expiration_date = $this->parseDate($idVerification['expiration_date'] ?? null);
                $kycData->issuing_state = $idVerification['issuing_state'] ?? null;
                $kycData->issuing_state_name = $idVerification['issuing_state_name'] ?? null;
            }
            
            // Extract address information
            if (!empty($idVerification)) {
                $kycData->address = $idVerification['address'] ?? null;
                $kycData->formatted_address = $idVerification['formatted_address'] ?? null;
                
                // Extract from parsed address if available
                $parsedAddress = $idVerification['parsed_address'] ?? [];
                if (!empty($parsedAddress)) {
                    $kycData->city = $parsedAddress['city'] ?? null;
                    $kycData->region = $parsedAddress['region'] ?? null;
                    $kycData->country = $parsedAddress['country'] ?? null;
                    $kycData->postal_code = $parsedAddress['postal_code'] ?? null;
                    
                    // Extract coordinates
                    if (!empty($parsedAddress['document_location'])) {
                        $location = $parsedAddress['document_location'];
                        $kycData->latitude = $this->parseDecimal($location['latitude'] ?? null);
                        $kycData->longitude = $this->parseDecimal($location['longitude'] ?? null);
                    }
                }
            }
            
            // Extract verification scores and results
            if (!empty($faceMatch)) {
                $kycData->face_match_score = $this->parseDecimal($faceMatch['score'] ?? null);
                $kycData->face_match_status = $faceMatch['status'] ?? null;
            }
            
            if (!empty($liveness)) {
                $kycData->liveness_score = $this->parseDecimal($liveness['score'] ?? null);
                $kycData->liveness_status = $liveness['status'] ?? null;
                $kycData->age_estimation = $this->parseDecimal($liveness['age_estimation'] ?? null);
            }
            
            // Set verification statuses
            $kycData->id_verification_status = $idVerification['status'] ?? null;
            $kycData->ip_analysis_status = $ipAnalysis['status'] ?? null;
            
            // Extract IP and device information
            if (!empty($ipAnalysis)) {
                $kycData->ip_address = $ipAnalysis['ip_address'] ?? null;
                $kycData->ip_country = $ipAnalysis['ip_country'] ?? null;
                $kycData->ip_city = $ipAnalysis['ip_city'] ?? null;
                $kycData->is_vpn_or_tor = $ipAnalysis['is_vpn_or_tor'] ?? false;
                $kycData->device_brand = $ipAnalysis['device_brand'] ?? null;
                $kycData->device_model = $ipAnalysis['device_model'] ?? null;
                $kycData->browser_family = $ipAnalysis['browser_family'] ?? null;
                $kycData->os_family = $ipAnalysis['os_family'] ?? null;
            }
            
            // Extract and download image URLs to permanent storage
            if (!empty($idVerification)) {
                $frontImageUrl = $idVerification['front_image'] ?? null;
                $backImageUrl = $idVerification['back_image'] ?? null;

                // Download and store front image permanently
                if ($frontImageUrl) {
                    $kycData->front_image_url = $this->downloadAndStoreImage(
                        $frontImageUrl,
                        $user->id,
                        $sessionId,
                        'front'
                    );
                }

                // Download and store back image permanently
                if ($backImageUrl) {
                    $kycData->back_image_url = $this->downloadAndStoreImage(
                        $backImageUrl,
                        $user->id,
                        $sessionId,
                        'back'
                    );
                }
            }

            // Extract live selfie from liveness section (this is the actual selfie taken during verification)
            if (!empty($liveness)) {
                $portraitImageUrl = $liveness['selfie_image'] ?? $liveness['face_image'] ?? $liveness['portrait_image'] ?? null;

                // If liveness doesn't have the selfie image, try other common field names
                if (!$portraitImageUrl) {
                    $portraitImageUrl = $liveness['user_image'] ?? $liveness['live_image'] ?? null;
                }

                // Fallback to ID verification portrait only if no liveness image found
                if (!$portraitImageUrl && !empty($idVerification['portrait_image'])) {
                    $portraitImageUrl = $idVerification['portrait_image'];
                }

                // Download and store portrait/selfie image permanently
                if ($portraitImageUrl) {
                    $kycData->portrait_image_url = $this->downloadAndStoreImage(
                        $portraitImageUrl,
                        $user->id,
                        $sessionId,
                        'portrait'
                    );
                }
            }

            if (!empty($liveness['video_url'])) {
                $videoUrl = $liveness['video_url'];
                // Download and store liveness video permanently
                if ($videoUrl) {
                    $kycData->liveness_video_url = $this->downloadAndStoreImage(
                        $videoUrl,
                        $user->id,
                        $sessionId,
                        'video',
                        'mp4'
                    );
                }
            }
            
            // Extract warnings from all sections
            $warnings = [];
            if (!empty($idVerification['warnings'])) {
                $warnings = array_merge($warnings, $idVerification['warnings']);
            }
            if (!empty($faceMatch['warnings'])) {
                $warnings = array_merge($warnings, $faceMatch['warnings']);
            }
            if (!empty($liveness['warnings'])) {
                $warnings = array_merge($warnings, $liveness['warnings']);
            }
            if (!empty($ipAnalysis['warnings'])) {
                $warnings = array_merge($warnings, $ipAnalysis['warnings']);
            }
            
            if (!empty($warnings)) {
                $kycData->warnings = $warnings;
            }
            
            // Set Didit creation timestamp if available
            if (isset($payload['created_at'])) {
                $kycData->didit_created_at = $this->parseTimestamp($payload['created_at']);
            }
            
            $kycData->save();
            
            Log::info('KYC data saved to kyc_data table', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'kyc_data_id' => $kycData->id,
                'status' => $mappedStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to save KYC data to kyc_data table', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw the exception as we don't want to break the webhook processing
            // The basic user status update should still succeed
        }
    }
    
    /**
     * Parse a date string into a Carbon date or null
     *
     * @param string|null $dateString
     * @return \Carbon\Carbon|null
     */
    private function parseDate(?string $dateString): ?\Carbon\Carbon
    {
        if (!$dateString) {
            return null;
        }
        
        try {
            return \Carbon\Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning('Failed to parse date', ['date_string' => $dateString, 'error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Parse a timestamp string into a Carbon datetime or null
     *
     * @param string|null $timestampString
     * @return \Carbon\Carbon|null
     */
    private function parseTimestamp(?string $timestampString): ?\Carbon\Carbon
    {
        if (!$timestampString) {
            return null;
        }
        
        try {
            return \Carbon\Carbon::parse($timestampString);
        } catch (\Exception $e) {
            Log::warning('Failed to parse timestamp', ['timestamp_string' => $timestampString, 'error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Parse a decimal value or return null
     *
     * @param mixed $value
     * @return float|null
     */
    private function parseDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * Download an image from a URL and store it permanently in Laravel storage
     *
     * @param string $url The URL of the image to download
     * @param int $userId The user ID
     * @param string $sessionId The KYC session ID
     * @param string $type The type of image (front, back, portrait, video)
     * @param string $extension The file extension (default: jpg)
     * @return string|null The storage path of the downloaded image or null on failure
     */
    private function downloadAndStoreImage(string $url, int $userId, string $sessionId, string $type, string $extension = 'jpg'): ?string
    {
        try {
            Log::info('Downloading KYC image', [
                'url' => $url,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'type' => $type
            ]);

            // Download the image from the URL
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Log::error('Failed to download KYC image', [
                    'url' => $url,
                    'status' => $response->status(),
                    'user_id' => $userId
                ]);
                return $url; // Return original URL as fallback
            }

            // Get the image content
            $imageContent = $response->body();

            if (empty($imageContent)) {
                Log::error('Downloaded KYC image is empty', [
                    'url' => $url,
                    'user_id' => $userId
                ]);
                return $url; // Return original URL as fallback
            }

            // Create a unique filename
            $filename = sprintf(
                'kyc/%d/%s/%s_%s.%s',
                $userId,
                $sessionId,
                $type,
                time(),
                $extension
            );

            // Store the image in the public disk so it's accessible via URL
            Storage::disk('public')->put($filename, $imageContent);

            // Generate the public URL for the stored image
            $storedPath = Storage::disk('public')->url($filename);

            Log::info('KYC image downloaded and stored successfully', [
                'original_url' => $url,
                'stored_path' => $storedPath,
                'user_id' => $userId,
                'type' => $type,
                'size' => strlen($imageContent)
            ]);

            return $storedPath;

        } catch (\Exception $e) {
            Log::error('Exception while downloading KYC image', [
                'url' => $url,
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return the original URL as fallback
            // This ensures the webhook doesn't fail even if image download fails
            return $url;
        }
    }
}
