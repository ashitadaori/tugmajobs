<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationReceived extends Notification
{
    use Queueable;

    protected $application;

    /**
     * Create a new notification instance.
     */
    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Only use database notifications for now (mail server not configured)
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->job->title;
        $applicantName = $this->application->user->name;

        return (new MailMessage)
            ->subject("New Application Received - {$jobTitle}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have received a new application for the position of {$jobTitle}.")
            ->line("**Applicant:** {$applicantName}")
            ->line("**Applied on:** {$this->application->created_at->format('M d, Y h:i A')}")
            ->action('View Application', route('employer.applications.show', $this->application->id))
            ->line('Review the application and respond to the candidate as soon as possible.');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        $jobTitle = $this->application->job->title;
        $applicantName = $this->application->user->name;
        
        return [
            'title' => 'New Application Received',
            'type' => 'new_application',
            'job_application_id' => $this->application->id,
            'job_id' => $this->application->job_id,
            'job_title' => $jobTitle,
            'applicant_name' => $applicantName,
            'applicant_id' => $this->application->user_id,
            'message' => $applicantName . ' has applied for "' . $jobTitle . '"',
            'action_url' => route('employer.applications.show', $this->application->id),
            'icon' => 'file-text',
            'color' => 'primary'
        ];
    }
}
