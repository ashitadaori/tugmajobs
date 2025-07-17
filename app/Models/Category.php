<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'icon'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = $category->slug ?? Str::slug($category->name);
            if (empty($category->icon)) {
                $iconMap = self::getIconMap();
                $category->icon = $iconMap[$category->slug] ?? '📋';
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function isParent()
    {
        return is_null($this->parent_id);
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    protected static function getIconMap()
    {
        return [
            'engineering' => '🔧',
            'design' => '🎨',
            'marketing' => '📢',
            'sales' => '💼',
            'human-resources' => '👥',
            'information-technology' => '💻',
            'software-development' => '⌨️',
            'web-development' => '🌐',
            'mobile-development' => '📱',
            'data-science' => '📊',
            'artificial-intelligence' => '🤖',
            'cloud-computing' => '☁️',
            'cybersecurity' => '🔒',
            'devops' => '⚙️',
            'ui-ux-design' => '🎨',
            'digital-marketing' => '📱',
            'content-writing' => '✍️',
            'project-management' => '📋',
            'business-analysis' => '📈',
            'customer-service' => '🤝',
            'finance' => '💰',
            'healthcare' => '⚕️',
            'education' => '📚'
        ];
    }

    // No need for getIconAttribute since we're storing icons in the database
}
