<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $job;
    protected $rejectionReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Job $job, string $rejectionReason)
    {
        $this->job = $job;
        $this->rejectionReason = $rejectionReason;
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
            ->subject('⚠️ Action Required: Job Posting Needs Revision')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your job posting "' . $this->job->title . '" requires some revisions before it can be published.')
            ->line('**Reason for Rejection:**')
            ->line($this->rejectionReason)
            ->line('**What to do next:**')
            ->line('1. Review the feedback above')
            ->line('2. Edit your job posting to address the concerns')
            ->line('3. Resubmit for approval')
            ->action('Edit Job Posting', route('employer.jobs.edit', $this->job->id))
            ->line('**Job Details:**')
            ->line('• Title: ' . $this->job->title)
            ->line('• Location: ' . $this->job->location)
            ->line('• Submitted: ' . $this->job->created_at->format('M d, Y'))
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you for your understanding!');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Job Posting Needs Revision',
            'type' => 'job_rejected',
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'message' => 'Your job posting "' . $this->job->title . '" needs revision. Please review the feedback and resubmit.',
            'rejection_reason' => $this->rejectionReason,
            'action_url' => route('employer.jobs.edit', $this->job->id),
            'icon' => 'exclamation-triangle',
            'color' => 'warning'
        ];
    }
}
