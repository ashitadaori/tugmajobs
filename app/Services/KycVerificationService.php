<?php

namespace App\Services;

use App\Models\KycVerification;
use Illuminate\Support\Facades\Log;

class KycVerificationService
{
    protected $diditService;

    public function __construct(DiditService $diditService)
    {
        $this->diditService = $diditService;
    }

    /**
     * Process and store verification data from DiDit
     * 
     * @param array $data Raw verification data
     * @return KycVerification|null
     */
    public function processVerificationData(array $data): ?KycVerification
    {
        $sessionId = $data['session_id'] ?? null;
        
        if (!$sessionId) {
            Log::error('Invalid verification data: missing session_id', $data);
            return null;
        }

        // Check if verification already exists
        $verification = KycVerification::where('session_id', $sessionId)->first();
        
        if (!$verification) {
            // Try to find user ID from vendor_data
            $userId = $data['vendor_data'] ?? $data['raw_data']['vendor_data'] ?? null;
            
            if (!$userId) {
                // Try metadata
                $userId = $data['metadata']['user_id'] ?? $data['raw_data']['metadata']['user_id'] ?? null;
            }
            
            if (!$userId) {
                Log::error('Cannot create KYC verification: no user ID found', $data);
                return null;
            }

            $verification = new KycVerification([
                'user_id' => $userId,
                'session_id' => $sessionId,
            ]);
        }

        // Update status
        $verification->status = $data['status'] ?? $verification->status;

        // Store raw data for complete record
        if (isset($data['raw_data'])) {
            $verification->raw_data = $data['raw_data'];
        }

        // Store verification data
        if (isset($data['verification_data'])) {
            $verification->verification_data = $data['verification_data'];
        }

        // Extract and store specific fields
        $this->extractIdentityData($verification, $data);

        // Save changes
        $verification->save();

        Log::info('KYC verification data saved', [
            'session_id' => $sessionId,
            'status' => $verification->status,
        ]);

        return $verification;
    }

    /**
     * Extract identity document and personal information from verification data
     * 
     * @param KycVerification $verification
     * @param array $data
     */
    protected function extractIdentityData(KycVerification $verification, array $data): void
    {
        // Check for document information in various locations
        $document = null;
        $person = null;

        // Try to get document and person data from extracted_data (new format)
        if (!empty($data['extracted_data'])) {
            $extractedData = $data['extracted_data'];
            
            if (!empty($extractedData['document'])) {
                $document = $extractedData['document'];
            }
            
            if (!empty($extractedData['person'])) {
                $person = $extractedData['person'];
            }
        }
        // Try verification_data (older format)
        else if (!empty($data['verification_data'])) {
            $verificationData = $data['verification_data'];
            
            if (!empty($verificationData['document'])) {
                $document = $verificationData['document'];
            }
            
            if (!empty($verificationData['person'])) {
                $person = $verificationData['person'];
            }
        }
        // Direct structure from API
        else if (!empty($data['result'])) {
            $result = $data['result'];
            
            if (!empty($result['document'])) {
                $document = $result['document'];
            }
            
            if (!empty($result['person'])) {
                $person = $result['person'];
            }
        }

        // Extract document information if available
        if ($document) {
            $verification->document_type = $document['type'] ?? $verification->document_type;
            $verification->document_number = $document['number'] ?? $verification->document_number;
        }

        // Extract person information if available
        if ($person) {
            // Handle name - try multiple formats
            $verification->firstname = $person['firstname'] ?? $person['first_name'] ?? $verification->firstname;
            $verification->lastname = $person['lastname'] ?? $person['last_name'] ?? $verification->lastname;

            // If no direct name fields but full_name is available, split it
            if ((empty($verification->firstname) || empty($verification->lastname)) && !empty($person['full_name'])) {
                $nameParts = explode(' ', trim($person['full_name']), 2);
                if (count($nameParts) > 0) {
                    $verification->firstname = $nameParts[0] ?? $verification->firstname;
                    $verification->lastname = $nameParts[1] ?? $verification->lastname;
                }
            }

            // Date of birth
            if (!empty($person['date_of_birth'])) {
                $verification->date_of_birth = $person['date_of_birth'];
            }

            // Gender
            if (!empty($person['gender'])) {
                $verification->gender = $person['gender'];
            }

            // Address
            if (!empty($person['address'])) {
                if (is_array($person['address'])) {
                    $verification->address = $this->formatAddress($person['address']);
                } else {
                    $verification->address = $person['address'];
                }
            }

            // Nationality
            if (!empty($person['nationality'])) {
                $verification->nationality = $person['nationality'];
            }
        }
    }

    /**
     * Format address data from DiDit into a string
     * 
     * @param array|string $address The address data from DiDit
     * @return string|null The formatted address
     */
    protected function formatAddress($address): ?string
    {
        if (empty($address)) {
            return null;
        }

        if (is_string($address)) {
            return $address;
        }

        $addressParts = [];

        // Add street address components
        if (!empty($address['street'])) {
            $addressParts[] = $address['street'];
        }

        // Add apartment/unit if available
        if (!empty($address['apartment'])) {
            $addressParts[] = $address['apartment'];
        }

        // Add city, region, postal code
        $locationParts = [];
        if (!empty($address['city'])) {
            $locationParts[] = $address['city'];
        }
        if (!empty($address['region'])) {
            $locationParts[] = $address['region'];
        }
        if (!empty($address['postal_code'])) {
            $locationParts[] = $address['postal_code'];
        }

        if (!empty($locationParts)) {
            $addressParts[] = implode(', ', $locationParts);
        }

        // Add country
        if (!empty($address['country'])) {
            $addressParts[] = $address['country'];
        }

        return !empty($addressParts) ? implode(', ', $addressParts) : null;
    }
}