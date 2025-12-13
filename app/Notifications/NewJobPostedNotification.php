<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Job;

class NewJobPostedNotification extends Notification
{
    use Queueable;

    protected $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // Insert directly into custom notifications table
        \DB::table('notifications')->insert([
            'user_id' => $notifiable->id,
            'title' => 'New Job Posted!',
            'message' => 'A new job opportunity is available: ' . $this->job->title . ' at ' . ($this->job->employer->company_name ?? $this->job->employer->name ?? 'Company'),
            'type' => 'new_job',
            'data' => json_encode([
                'job_id' => $this->job->id,
                'job_title' => $this->job->title,
                'company_name' => $this->job->employer->company_name ?? $this->job->employer->name ?? 'Company',
                'location' => $this->job->location,
                'job_type' => $this->job->jobType->name ?? 'Full Time',
                'category' => $this->job->category->name ?? 'General',
                'status' => 'new_job'
            ]),
            'action_url' => route('jobDetail', $this->job->id),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Return empty array since we're handling insertion manually
        return [];
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
