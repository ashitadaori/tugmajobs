<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosterTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get all posters using this template
     */
    public function posters()
    {
        return $this->hasMany(Poster::class, 'template_id');
    }

    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Get the next template in rotation
     */
    public static function getNextTemplate()
    {
        $lastUsedId = PosterSetting::get('last_template_id', 0);
        $templates = self::active()->ordered()->get();

        if ($templates->isEmpty()) {
            return null;
        }

        // Find the next template in rotation
        $currentIndex = $templates->search(function ($template) use ($lastUsedId) {
            return $template->id == $lastUsedId;
        });

        // If not found or at the end, start from beginning
        if ($currentIndex === false || $currentIndex >= $templates->count() - 1) {
            $nextTemplate = $templates->first();
        } else {
            $nextTemplate = $templates[$currentIndex + 1];
        }

        return $nextTemplate;
    }

    /**
     * Mark this template as the last used
     */
    public function markAsUsed()
    {
        PosterSetting::set('last_template_id', $this->id);
    }
}
