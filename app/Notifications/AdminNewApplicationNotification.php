<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewApplicationNotification extends Notification
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
        // Only use mail channel - in-app notifications are created separately in the custom notifications table
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->job->title;
        $applicantName = $this->application->user->name;
        $applicantEmail = $this->application->user->email;
        $employerName = $this->application->job->employer->name ?? 'Unknown Employer';

        // Get company name
        $companyName = 'Unknown Company';
        if ($this->application->job->employer) {
            $employer = $this->application->job->employer;
            $employerProfile = \App\Models\Employer::where('user_id', $employer->id)->first();
            $companyName = $employerProfile->company_name ?? $employer->name;
        }

        return (new MailMessage)
            ->subject("New Job Application - {$jobTitle}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new job application has been submitted that requires your attention.")
            ->line("**Job Position:** {$jobTitle}")
            ->line("**Company:** {$companyName}")
            ->line("**Applicant:** {$applicantName}")
            ->line("**Applicant Email:** {$applicantEmail}")
            ->line("**Applied on:** {$this->application->created_at->format('M d, Y h:i A')}")
            ->action('View Applicants', route('admin.jobs.applicants', $this->application->job_id))
            ->line('Please review the application and take appropriate action.');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        $jobTitle = $this->application->job->title;
        $applicantName = $this->application->user->name;

        // Get company name
        $companyName = 'Unknown Company';
        if ($this->application->job->employer) {
            $employer = $this->application->job->employer;
            $employerProfile = \App\Models\Employer::where('user_id', $employer->id)->first();
            $companyName = $employerProfile->company_name ?? $employer->name;
        }

        return [
            'title' => 'New Job Application Received',
            'type' => 'admin_new_application',
            'job_application_id' => $this->application->id,
            'job_id' => $this->application->job_id,
            'job_title' => $jobTitle,
            'applicant_name' => $applicantName,
            'applicant_id' => $this->application->user_id,
            'company_name' => $companyName,
            'message' => $applicantName . ' has applied for "' . $jobTitle . '" at ' . $companyName,
            'action_url' => route('admin.jobs.applicants', $this->application->job_id),
            'icon' => 'user-plus',
            'color' => 'info'
        ];
    }
}
