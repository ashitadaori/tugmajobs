<?php

namespace App\Services;

use App\Models\User;
use App\Models\Jobseeker;
use App\Models\KycData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Service to sync KYC verification data to jobseeker profile
 *
 * This service automatically populates the jobseeker profile with
 * personal information extracted from the KYC verification process.
 */
class KycProfileSyncService
{
    /**
     * Sync KYC data to the jobseeker profile
     *
     * @param User $user The user whose profile should be updated
     * @param KycData $kycData The KYC data to sync from
     * @return array Result with status and message
     */
    public function syncKycToProfile(User $user, KycData $kycData): array
    {
        // Only sync for jobseekers
        if (!$user->isJobSeeker()) {
            Log::info('KYC Profile Sync skipped - user is not a jobseeker', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);

            return [
                'success' => false,
                'message' => 'User is not a jobseeker',
                'synced_fields' => [],
            ];
        }

        try {
            return DB::transaction(function () use ($user, $kycData) {
                // Get or create jobseeker profile
                $jobseeker = $user->jobseeker;

                if (!$jobseeker) {
                    $jobseeker = new Jobseeker();
                    $jobseeker->user_id = $user->id;
                    Log::info('Creating new jobseeker profile for KYC sync', [
                        'user_id' => $user->id,
                    ]);
                }

                // Track which fields were synced
                $syncedFields = [];
                $skippedFields = [];

                // Sync personal information (only if profile field is empty)
                $fieldMappings = $this->getFieldMappings();

                foreach ($fieldMappings as $kycField => $profileField) {
                    $kycValue = $kycData->{$kycField};
                    $currentValue = $jobseeker->{$profileField};

                    // Only sync if KYC has data and profile field is empty
                    if (!empty($kycValue) && empty($currentValue)) {
                        $jobseeker->{$profileField} = $kycValue;
                        $syncedFields[$profileField] = $kycValue;

                        Log::debug('KYC field synced to profile', [
                            'user_id' => $user->id,
                            'kyc_field' => $kycField,
                            'profile_field' => $profileField,
                            'value' => $this->maskSensitiveValue($profileField, $kycValue),
                        ]);
                    } elseif (!empty($currentValue)) {
                        $skippedFields[$profileField] = 'already_has_value';
                    }
                }

                // Handle special case: full_name split into first_name and last_name
                if (empty($jobseeker->first_name) && empty($jobseeker->last_name) && !empty($kycData->full_name)) {
                    $nameParts = $this->parseFullName($kycData->full_name);

                    if (!empty($nameParts['first_name'])) {
                        $jobseeker->first_name = $nameParts['first_name'];
                        $syncedFields['first_name'] = $nameParts['first_name'];
                    }
                    if (!empty($nameParts['last_name'])) {
                        $jobseeker->last_name = $nameParts['last_name'];
                        $syncedFields['last_name'] = $nameParts['last_name'];
                    }
                    if (!empty($nameParts['middle_name'])) {
                        $jobseeker->middle_name = $nameParts['middle_name'];
                        $syncedFields['middle_name'] = $nameParts['middle_name'];
                    }
                }

                // Handle address: use formatted_address or address for current_address
                if (empty($jobseeker->current_address)) {
                    $address = $kycData->formatted_address ?? $kycData->address;
                    if (!empty($address)) {
                        $jobseeker->current_address = $address;
                        $syncedFields['current_address'] = $address;
                    }
                }

                // Handle region -> state mapping
                if (empty($jobseeker->state) && !empty($kycData->region)) {
                    $jobseeker->state = $kycData->region;
                    $syncedFields['state'] = $kycData->region;
                }

                // Save the profile if any fields were synced
                if (!empty($syncedFields)) {
                    $jobseeker->save();

                    // Recalculate profile completion percentage
                    $jobseeker->calculateProfileCompletion();

                    Log::info('KYC data synced to jobseeker profile successfully', [
                        'user_id' => $user->id,
                        'jobseeker_id' => $jobseeker->id,
                        'synced_fields_count' => count($syncedFields),
                        'synced_fields' => array_keys($syncedFields),
                        'skipped_fields' => array_keys($skippedFields),
                        'new_completion_percentage' => $jobseeker->profile_completion_percentage,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Profile updated with KYC data',
                        'synced_fields' => $syncedFields,
                        'skipped_fields' => $skippedFields,
                        'profile_completion' => $jobseeker->profile_completion_percentage,
                    ];
                }

                Log::info('No fields synced from KYC to profile - all fields already populated', [
                    'user_id' => $user->id,
                    'skipped_fields' => array_keys($skippedFields),
                ]);

                return [
                    'success' => true,
                    'message' => 'No new fields to sync - profile already has data',
                    'synced_fields' => [],
                    'skipped_fields' => $skippedFields,
                ];
            });

        } catch (\Exception $e) {
            Log::error('Failed to sync KYC data to jobseeker profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to sync profile: ' . $e->getMessage(),
                'synced_fields' => [],
            ];
        }
    }

    /**
     * Get the field mappings from KycData to Jobseeker profile
     *
     * @return array KYC field => Profile field
     */
    protected function getFieldMappings(): array
    {
        return [
            // Personal Information
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'date_of_birth' => 'date_of_birth',
            'gender' => 'gender',
            'nationality' => 'nationality',
            'marital_status' => 'marital_status',

            // Address Information
            'city' => 'city',
            'country' => 'country',
            'postal_code' => 'postal_code',
        ];
    }

    /**
     * Parse a full name into first, middle, and last name components
     *
     * @param string $fullName
     * @return array
     */
    protected function parseFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));
        $count = count($parts);

        if ($count === 0) {
            return ['first_name' => '', 'middle_name' => '', 'last_name' => ''];
        }

        if ($count === 1) {
            return [
                'first_name' => $parts[0],
                'middle_name' => '',
                'last_name' => '',
            ];
        }

        if ($count === 2) {
            return [
                'first_name' => $parts[0],
                'middle_name' => '',
                'last_name' => $parts[1],
            ];
        }

        // For 3+ parts: first is first name, last is last name, middle are middle names
        return [
            'first_name' => $parts[0],
            'middle_name' => implode(' ', array_slice($parts, 1, -1)),
            'last_name' => end($parts),
        ];
    }

    /**
     * Mask sensitive values for logging
     *
     * @param string $field
     * @param mixed $value
     * @return string
     */
    protected function maskSensitiveValue(string $field, $value): string
    {
        // Mask date of birth partially
        if ($field === 'date_of_birth') {
            return '****-**-' . substr($value, -2);
        }

        // Mask address partially
        if (in_array($field, ['current_address', 'permanent_address'])) {
            return substr($value, 0, 10) . '***';
        }

        // Return as-is for non-sensitive fields
        return (string) $value;
    }

    /**
     * Get a summary of what fields can be synced from KYC to profile
     *
     * @param User $user
     * @param KycData $kycData
     * @return array
     */
    public function getSyncPreview(User $user, KycData $kycData): array
    {
        if (!$user->isJobSeeker()) {
            return ['can_sync' => false, 'reason' => 'User is not a jobseeker'];
        }

        $jobseeker = $user->jobseeker;
        $canSync = [];
        $alreadyFilled = [];

        $fieldMappings = $this->getFieldMappings();

        foreach ($fieldMappings as $kycField => $profileField) {
            $kycValue = $kycData->{$kycField};
            $currentValue = $jobseeker ? $jobseeker->{$profileField} : null;

            if (!empty($kycValue)) {
                if (empty($currentValue)) {
                    $canSync[$profileField] = $kycValue;
                } else {
                    $alreadyFilled[$profileField] = $currentValue;
                }
            }
        }

        return [
            'can_sync' => !empty($canSync),
            'fields_to_sync' => $canSync,
            'fields_already_filled' => $alreadyFilled,
        ];
    }
}
