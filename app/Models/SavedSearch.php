<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'filters',
        'email_alerts',
        'last_run_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'email_alerts' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    /**
     * Get the user that owns the saved search.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a summary of the search filters for display.
     */
    public function getFiltersSummaryAttribute(): string
    {
        $summary = [];

        if (!empty($this->filters['q'])) {
            $summary[] = "Keyword: {$this->filters['q']}";
        }

        if (!empty($this->filters['location'])) {
            $summary[] = "Location: {$this->filters['location']}";
        }

        if (!empty($this->filters['category_id'])) {
            $category = Category::find($this->filters['category_id']);
            if ($category) {
                $summary[] = "Category: {$category->name}";
            }
        }

        if (!empty($this->filters['job_type_id'])) {
            $jobType = JobType::find($this->filters['job_type_id']);
            if ($jobType) {
                $summary[] = "Type: {$jobType->name}";
            }
        }

        return empty($summary) ? 'All Jobs' : implode(', ', $summary);
    }
}
