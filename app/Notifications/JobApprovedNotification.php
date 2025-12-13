<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $job;

    /**
     * Create a new notification instance.
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Only use database notifications for now (email requires SMTP setup)
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Your Job Posting Has Been Approved!')
            ->greeting('Great News!')
            ->line('Your job posting "' . $this->job->title . '" has been approved and is now live on TugmaJobs!')
            ->line('Job seekers can now view and apply to your position.')
            ->action('View Job Posting', route('employer.jobs.index'))
            ->line('**Job Details:**')
            ->line('• Title: ' . $this->job->title)
            ->line('• Location: ' . $this->job->location)
            ->line('• Job Type: ' . ($this->job->jobType->name ?? 'N/A'))
            ->line('• Posted: ' . $this->job->created_at->format('M d, Y'))
            ->line('Thank you for using TugmaJobs to find great talent!');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Job Posting Approved!',
            'type' => 'job_approved',
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'message' => 'Your job posting "' . $this->job->title . '" has been approved and is now live!',
            'action_url' => route('employer.jobs.index'),
            'icon' => 'check-circle',
            'color' => 'success'
        ];
    }
}
