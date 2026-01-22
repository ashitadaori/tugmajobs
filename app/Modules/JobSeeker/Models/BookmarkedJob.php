<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookmarkedJob extends Model
{
    use HasFactory;

    protected $table = 'bookmarked_jobs';

    protected $fillable = [
        'user_id',
        'job_id'
    ];

    /**
     * Get the user that bookmarked the job.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job that was bookmarked.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
