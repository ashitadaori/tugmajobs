<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KycDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'document_file',
        'status',
        'rejection_reason'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 