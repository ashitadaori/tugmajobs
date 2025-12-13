<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    use Queueable;

    protected $application;
    protected $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct(JobApplication $application, $notes = null)
    {
        $this->application = $application;
        $this->notes = $notes;
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
     * Get the company name from the job (handles both employer and admin posted jobs)
     */
    protected function getCompanyName(): string
    {
        $job = $this->application->job;

        // Check if job has a company directly linked
        if ($job->company_id && $job->company) {
            return $job->company->name;
        }

        // Check if job has company_name field
        if ($job->company_name) {
            return $job->company_name;
        }

        // Check if posted by employer with profile
        if ($job->employer && $job->employer->employerProfile && $job->employer->employerProfile->company_name) {
            return $job->employer->employerProfile->company_name;
        }

        // Check if posted by admin - use admin's name or PESO Office
        if ($job->posted_by_admin && $job->employer) {
            return $job->employer->name ?? 'PESO Office';
        }

        return 'the employer';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = ucfirst($this->application->status);
        $jobTitle = $this->application->job->title;
        $companyName = $this->getCompanyName();

        $message = (new MailMessage)
            ->subject("Job Application Status Updated - {$statusText}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your job application for the position of {$jobTitle} at {$companyName} has been {$this->application->status}.");

        // Add employer feedback/notes if provided
        if ($this->notes) {
            $message->line("**Feedback from employer:**")
                   ->line($this->notes);
        }

        // Add status-specific messages
        $message->when($this->application->status === 'approved', function ($message) {
                return $message->line('Congratulations! The employer will contact you soon with further details.');
            })
            ->when($this->application->status === 'rejected', function ($message) {
                return $message->line('Thank you for your interest. We encourage you to apply for other positions that match your skills.');
            });

        return $message->action('View Application', route('account.myJobApplications'))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->application->status;
        $jobTitle = $this->application->job->title;
        $companyName = $this->getCompanyName();
        
        // Set title, message, icon, and color based on status
        if ($status === 'rejected') {
            $title = 'Application Rejected';
            $message = 'Your application for "' . $jobTitle . '" at ' . $companyName . ' was not successful.';
            if ($this->notes) {
                $message .= ' Feedback: ' . $this->notes;
            }
            $icon = 'times-circle';
            $color = 'danger';
        } elseif ($status === 'approved') {
            $title = 'Application Approved!';
            $message = 'Great news! Your application for "' . $jobTitle . '" at ' . $companyName . ' has been approved!';
            if ($this->notes) {
                $message .= ' Message: ' . $this->notes;
            }
            $icon = 'check-circle';
            $color = 'success';
        } else {
            $title = 'Application Status Updated';
            $message = 'Your application for "' . $jobTitle . '" at ' . $companyName . ' has been updated.';
            $icon = 'info-circle';
            $color = 'info';
        }
        
        return [
            'title' => $title,
            'type' => 'application_status',
            'job_application_id' => $this->application->id,
            'job_id' => $this->application->job_id,
            'job_title' => $jobTitle,
            'company_name' => $companyName,
            'message' => $message,
            'status' => $status,
            'rejection_reason' => $this->notes,
            'action_url' => route('account.myJobApplications'),
            'icon' => $icon,
            'color' => $color
        ];
    }
} 