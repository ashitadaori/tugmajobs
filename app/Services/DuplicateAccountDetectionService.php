<?php

namespace App\Services;

use App\Models\KycData;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DuplicateAccountDetectionService
{
    /**
     * Check if the identity has already been verified by another user
     *
     * @param int $currentUserId The user attempting verification
     * @param array $verificationData The KYC verification data from Didit
     * @return array{is_duplicate: bool, existing_user_id: ?int, match_type: ?string, message: ?string}
     */
    public function checkForDuplicateIdentity(int $currentUserId, array $verificationData): array
    {
        $decision = $verificationData['decision'] ?? [];
        $idVerification = $decision['id_verification'] ?? [];

        $documentNumber = $idVerification['document_number'] ?? null;
        $firstName = $idVerification['first_name'] ?? null;
        $lastName = $idVerification['last_name'] ?? null;
        $dateOfBirth = $idVerification['date_of_birth'] ?? null;

        // Check 1: Exact document number match (strongest indicator)
        if ($documentNumber) {
            $existingKyc = KycData::where('document_number', $documentNumber)
                ->where('user_id', '!=', $currentUserId)
                ->whereIn('status', ['verified', 'approved', 'completed'])
                ->first();

            if ($existingKyc) {
                Log::warning('Duplicate account detected: Same document number', [
                    'current_user_id' => $currentUserId,
                    'existing_user_id' => $existingKyc->user_id,
                    'document_number' => $this->maskDocumentNumber($documentNumber),
                ]);

                return [
                    'is_duplicate' => true,
                    'existing_user_id' => $existingKyc->user_id,
                    'match_type' => 'document_number',
                    'message' => 'This ID document has already been used to verify another account.',
                ];
            }
        }

        // Check 2: Combination of name + date of birth (secondary check)
        if ($firstName && $lastName && $dateOfBirth) {
            $existingKyc = KycData::where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->where('date_of_birth', $dateOfBirth)
                ->where('user_id', '!=', $currentUserId)
                ->whereIn('status', ['verified', 'approved', 'completed'])
                ->first();

            if ($existingKyc) {
                Log::warning('Duplicate account detected: Same name and date of birth', [
                    'current_user_id' => $currentUserId,
                    'existing_user_id' => $existingKyc->user_id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);

                return [
                    'is_duplicate' => true,
                    'existing_user_id' => $existingKyc->user_id,
                    'match_type' => 'name_dob',
                    'message' => 'An account with this identity information already exists.',
                ];
            }
        }

        // No duplicate found
        return [
            'is_duplicate' => false,
            'existing_user_id' => null,
            'match_type' => null,
            'message' => null,
        ];
    }

    /**
     * Check if the current user already has a verified identity
     * (to prevent re-verification with different documents)
     *
     * @param int $userId
     * @return bool
     */
    public function userAlreadyVerified(int $userId): bool
    {
        return KycData::where('user_id', $userId)
            ->whereIn('status', ['verified', 'approved', 'completed'])
            ->exists();
    }

    /**
     * Get the existing verified user for a given identity
     *
     * @param string $documentNumber
     * @return User|null
     */
    public function getExistingVerifiedUser(string $documentNumber): ?User
    {
        $kycData = KycData::where('document_number', $documentNumber)
            ->whereIn('status', ['verified', 'approved', 'completed'])
            ->first();

        return $kycData ? $kycData->user : null;
    }

    /**
     * Mask a document number for logging (privacy)
     *
     * @param string $documentNumber
     * @return string
     */
    private function maskDocumentNumber(string $documentNumber): string
    {
        $length = strlen($documentNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $visibleChars = 4;
        $masked = str_repeat('*', $length - $visibleChars) . substr($documentNumber, -$visibleChars);

        return $masked;
    }

    /**
     * Get details about the duplicate for admin review
     *
     * @param int $currentUserId
     * @param int $existingUserId
     * @return array
     */
    public function getDuplicateDetails(int $currentUserId, int $existingUserId): array
    {
        $currentUser = User::find($currentUserId);
        $existingUser = User::find($existingUserId);

        $existingKyc = KycData::where('user_id', $existingUserId)
            ->whereIn('status', ['verified', 'approved', 'completed'])
            ->first();

        return [
            'current_user' => [
                'id' => $currentUserId,
                'email' => $currentUser?->email,
                'name' => $currentUser?->name,
                'role' => $currentUser?->role,
                'created_at' => $currentUser?->created_at,
            ],
            'existing_user' => [
                'id' => $existingUserId,
                'email' => $existingUser?->email,
                'name' => $existingUser?->name,
                'role' => $existingUser?->role,
                'created_at' => $existingUser?->created_at,
                'kyc_verified_at' => $existingUser?->kyc_verified_at,
            ],
            'kyc_data' => [
                'document_type' => $existingKyc?->document_type,
                'document_number_masked' => $existingKyc ? $this->maskDocumentNumber($existingKyc->document_number ?? '') : null,
                'full_name' => $existingKyc?->display_name,
                'verified_at' => $existingKyc?->verified_at,
            ],
        ];
    }
}
