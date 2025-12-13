<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poster extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'created_by',
        'job_title',
        'requirements',
        'company_name',
    ];

    /**
     * Get the template used for this poster
     */
    public function template()
    {
        return $this->belongsTo(PosterTemplate::class, 'template_id');
    }

    /**
     * Get the admin who created this poster
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
