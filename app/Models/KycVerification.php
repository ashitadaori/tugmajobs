<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycVerification extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'verification_data',
        'document_type',
        'document_number',
        'firstname',
        'lastname',
        'date_of_birth',
        'gender',
        'address',
        'nationality',
        'raw_data',
        'verified_at',
        'completed_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verification_data' => 'array',
        'raw_data' => 'array',
        'date_of_birth' => 'date',
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
        'gender' => 'string',
        'address' => 'string',
    ];
    
    /**
     * Get the user that owns the KYC verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
