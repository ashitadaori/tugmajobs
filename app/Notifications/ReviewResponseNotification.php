<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewResponseNotification extends Notification
{
    use Queueable;

    protected $review;
    protected $action; // 'posted', 'updated', 'deleted'

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Review $review, $action = 'posted')
    {
        $this->review = $review;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $employer = $this->review->employer;
        $companyName = $employer->employerProfile->company_name ?? $employer->name;
        
        $subject = match($this->action) {
            'updated' => "{$companyName} updated their response to your review",
            'deleted' => "{$companyName} removed their response to your review",
            default => "{$companyName} responded to your review"
        };

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("The employer has {$this->action} a response to your review.");

        if ($this->action !== 'deleted' && $this->review->employer_response) {
            $mailMessage->line('**Their Response:**')
                       ->line('"' . $this->review->employer_response . '"');
        }

        $mailMessage->action('View Review', url('/account/my-job-applications'))
                   ->line('Thank you for sharing your feedback!');

        return $mailMessage;
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        try {
            $employer = $this->review->employer;
            
            // Safely get company name
            $companyName = 'The employer';
            if ($employer) {
                if ($employer->employerProfile && $employer->employerProfile->company_name) {
                    $companyName = $employer->employerProfile->company_name;
                } else {
                    $companyName = $employer->name;
                }
            }
            
            $title = match($this->action) {
                'updated' => "Response Updated",
                'deleted' => "Response Removed",
                default => "New Response to Your Review"
            };
            
            $message = match($this->action) {
                'updated' => "{$companyName} updated their response to your review",
                'deleted' => "{$companyName} removed their response to your review",
                default => "{$companyName} responded to your review"
            };
            
            // Add response preview if available
            if ($this->action !== 'deleted' && $this->review->employer_response) {
                $responsePreview = strlen($this->review->employer_response) > 100 
                    ? substr($this->review->employer_response, 0, 100) . '...' 
                    : $this->review->employer_response;
                $message .= ': "' . $responsePreview . '"';
            }

            return [
                'user_id' => $notifiable->id,
                'title' => $title,
                'message' => $message,
                'type' => 'review_response',
                'data' => json_encode([
                    'action' => $this->action,
                    'review_id' => $this->review->id,
                    'employer_id' => $this->review->employer_id,
                    'company_name' => $companyName,
                    'review_type' => $this->review->review_type,
                    'job_id' => $this->review->job_id,
                    'job_title' => $this->review->job ? $this->review->job->title : null,
                    'response' => $this->action !== 'deleted' ? $this->review->employer_response : null,
                    'icon' => 'fas fa-reply',
                    'color' => 'primary'
                ]),
                'action_url' => url('/account/my-job-applications')
            ];
        } catch (\Exception $e) {
            \Log::error('Review notification error: ' . $e->getMessage());
            
            // Return basic notification if there's an error
            return [
                'user_id' => $notifiable->id,
                'title' => 'New Response',
                'message' => 'An employer responded to your review',
                'type' => 'review_response',
                'data' => json_encode([
                    'action' => $this->action,
                    'review_id' => $this->review->id,
                    'icon' => 'fas fa-reply',
                    'color' => 'primary'
                ]),
                'action_url' => url('/account/my-job-applications')
            ];
        }
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
