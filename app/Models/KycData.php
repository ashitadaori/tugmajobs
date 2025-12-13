<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'didit_status',
        'first_name',
        'last_name',
        'full_name',
        'date_of_birth',
        'gender',
        'nationality',
        'place_of_birth',
        'marital_status',
        'document_type',
        'document_number',
        'document_issue_date',
        'document_expiration_date',
        'issuing_state',
        'issuing_state_name',
        'address',
        'formatted_address',
        'city',
        'region',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'face_match_score',
        'face_match_status',
        'liveness_score',
        'liveness_status',
        'id_verification_status',
        'ip_analysis_status',
        'age_estimation',
        'ip_address',
        'ip_country',
        'ip_city',
        'is_vpn_or_tor',
        'device_brand',
        'device_model',
        'browser_family',
        'os_family',
        'front_image_url',
        'back_image_url',
        'portrait_image_url',
        'liveness_video_url',
        'raw_payload',
        'warnings',
        'verification_method',
        'didit_created_at',
        'verified_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'document_issue_date' => 'date',
        'document_expiration_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'face_match_score' => 'decimal:2',
        'liveness_score' => 'decimal:2',
        'age_estimation' => 'decimal:2',
        'is_vpn_or_tor' => 'boolean',
        'raw_payload' => 'array',
        'warnings' => 'array',
        'didit_created_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the KYC data.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the verification was successful.
     */
    public function isVerified(): bool
    {
        return in_array($this->status, ['verified', 'approved', 'completed']);
    }

    /**
     * Check if the verification failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'rejected', 'error']);
    }

    /**
     * Get formatted address.
     */
    public function getFullAddressAttribute(): string
    {
        return $this->formatted_address ?: $this->address ?: 'Address not available';
    }

    /**
     * Get full name from first and last name if full_name is not available.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->full_name) {
            return $this->full_name;
        }
        
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        
        return $this->first_name ?: 'Name not available';
    }

    /**
     * Calculate age from date of birth.
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        
        return $this->date_of_birth->age;
    }

    /**
     * Get verification score summary.
     */
    public function getVerificationScoreAttribute(): array
    {
        return [
            'face_match' => $this->face_match_score,
            'liveness' => $this->liveness_score,
            'overall' => $this->calculateOverallScore(),
        ];
    }

    /**
     * Calculate overall verification score.
     */
    private function calculateOverallScore(): ?float
    {
        $scores = array_filter([
            $this->face_match_score,
            $this->liveness_score,
        ]);
        
        if (empty($scores)) {
            return null;
        }
        
        return round(array_sum($scores) / count($scores), 2);
    }
}
