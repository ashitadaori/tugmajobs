<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification implements ShouldQueue
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = ucfirst($this->application->status);
        $jobTitle = $this->application->job->title;
        $companyName = $this->application->job->employer->employerProfile->company_name;

        return (new MailMessage)
            ->subject("Job Application Status Updated - {$statusText}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your job application for the position of {$jobTitle} at {$companyName} has been {$this->application->status}.")
            ->when($this->application->status === 'approved', function ($message) {
                return $message->line('Congratulations! The employer will contact you soon with further details.');
            })
            ->when($this->application->status === 'rejected', function ($message) {
                return $message->line('Thank you for your interest. We encourage you to apply for other positions that match your skills.');
            })
            ->action('View Application', route('account.myJobApplications'))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'job_application_id' => $this->application->id,
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job->title,
            'company_name' => $this->application->job->employer->employerProfile->company_name,
            'status' => $this->application->status,
            'updated_at' => $this->application->updated_at->toIso8601String(),
        ];
    }
} 