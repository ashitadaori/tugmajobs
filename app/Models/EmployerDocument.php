<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_name', 
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'status',
        'admin_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Document types configuration
     */
    public static function getDocumentTypes(): array
    {
        return [
            'business_registration' => [
                'label' => 'Business Registration Certificate',
                'description' => 'Official business registration document',
                'required' => true,
            ],
            'tax_certificate' => [
                'label' => 'Tax Identification Certificate',
                'description' => 'Tax ID or Certificate from tax authority',
                'required' => true,
            ],
            'business_license' => [
                'label' => 'Business License',
                'description' => 'Operating license for your business',
                'required' => false,
            ],
            'incorporation_documents' => [
                'label' => 'Articles of Incorporation',
                'description' => 'Company incorporation documents',
                'required' => false,
            ],
            'bank_statement' => [
                'label' => 'Bank Statement',
                'description' => 'Recent business bank statement',
                'required' => false,
            ],
            'other' => [
                'label' => 'Other Supporting Documents',
                'description' => 'Any other relevant business documents',
                'required' => false,
            ],
        ];
    }

    /**
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the document.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the document type configuration.
     */
    public function getDocumentTypeConfigAttribute(): array
    {
        return static::getDocumentTypes()[$this->document_type] ?? [];
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-warning',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending Review',
        };
    }

    /**
     * Check if document is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if document is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if document is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Approve the document.
     */
    public function approve(User $reviewer, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Reject the document.
     */
    public function reject(User $reviewer, string $notes): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Scope for approved documents.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending documents.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for rejected documents.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for required documents.
     */
    public function scopeRequired($query)
    {
        $requiredTypes = collect(static::getDocumentTypes())
            ->filter(fn($config) => $config['required'])
            ->keys()
            ->toArray();
            
        return $query->whereIn('document_type', $requiredTypes);
    }
}
