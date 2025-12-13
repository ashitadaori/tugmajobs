<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->application;
        $job = $application->job;
        $employer = $job->employer->employerProfile ?? null;
        $companyName = $employer ? $employer->company_name : 'Unknown Company';

        $interviewDate = $application->interview_date->format('F d, Y');
        $interviewTime = $application->interview_time;
        $interviewLocation = $application->interview_location;
        $interviewType = $application->getInterviewTypeName();

        return (new MailMessage)
            ->subject('Interview Scheduled - ' . $job->title)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your interview has been scheduled for the position:')
            ->line('**' . $job->title . '** at **' . $companyName . '**')
            ->line('')
            ->line('**Interview Details:**')
            ->line('Date: ' . $interviewDate)
            ->line('Time: ' . $interviewTime)
            ->line('Location: ' . $interviewLocation)
            ->line('Type: ' . $interviewType)
            ->when($application->interview_notes, function ($mail) use ($application) {
                return $mail->line('')
                           ->line('**Additional Notes:**')
                           ->line($application->interview_notes);
            })
            ->action('View Application Details', route('account.myJobApplications'))
            ->line('')
            ->line('Please make sure to arrive on time and prepared for your interview.')
            ->line('Good luck!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $application = $this->application;
        $job = $application->job;
        $employer = $job->employer->employerProfile ?? null;

        return [
            'title' => 'Interview Scheduled!',
            'message' => 'Your interview for "' . $job->title . '" has been scheduled.',
            'job_application_id' => $application->id,
            'job_id' => $job->id,
            'job_title' => $job->title,
            'company_name' => $employer ? $employer->company_name : 'Unknown Company',
            'interview_date' => $application->interview_date->toDateString(),
            'interview_time' => $application->interview_time,
            'interview_location' => $application->interview_location,
            'interview_type' => $application->interview_type,
            'interview_notes' => $application->interview_notes,
            'type' => 'interview_scheduled',
            'action_url' => route('account.myJobApplications'),
        ];
    }
}
