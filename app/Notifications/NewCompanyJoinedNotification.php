<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCompanyJoinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $company;
    protected $companyType;

    /**
     * Create a new notification instance.
     */
    public function __construct($company, $companyType = 'standalone')
    {
        $this->company = $company;
        $this->companyType = $companyType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $companyName = $this->companyType === 'standalone' 
            ? $this->company->name 
            : $this->company->company_name;

        return (new MailMessage)
            ->subject('New Company Joined - ' . $companyName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new company has joined our platform!')
            ->line('**' . $companyName . '** is now hiring.')
            ->line('Check out their profile and explore new job opportunities.')
            ->action('View Company', url('/companies/' . $this->company->id))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $companyName = $this->companyType === 'standalone' 
            ? $this->company->name 
            : $this->company->company_name;

        $companyLogo = $this->companyType === 'standalone'
            ? $this->company->logo
            : $this->company->company_logo;

        // Use the jobseeker company show route
        $companyUrl = route('companies.show', $this->company->id);

        return [
            'title' => '🎉 New Company Joined!',
            'message' => $companyName . ' is now hiring! Check out their profile and explore new opportunities.',
            'type' => 'new_company',
            'company_id' => $this->company->id,
            'company_name' => $companyName,
            'company_logo' => $companyLogo,
            'company_type' => $this->companyType,
            'url' => $companyUrl,
            'action_url' => $companyUrl,
            'icon' => 'building',
            'color' => 'info'
        ];
    }
}
