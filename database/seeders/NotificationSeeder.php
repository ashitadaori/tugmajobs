<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        foreach ($users as $user) {
            // Create sample notifications for each user
            $this->createSampleNotifications($user);
        }
    }
    
    /**
     * Create sample notifications for a user
     */
    private function createSampleNotifications(User $user): void
    {
        $notificationTypes = [
            'job_application' => [
                'title' => 'New Application',
                'message' => 'You have a new application for Software Engineer position',
                'icon' => 'fas fa-file-alt text-primary'
            ],
            'job_saved' => [
                'title' => 'Job Saved',
                'message' => 'You saved Frontend Developer position',
                'icon' => 'fas fa-heart text-danger'
            ],
            'application_status' => [
                'title' => 'Application Status Updated',
                'message' => 'Your application for UX Designer was reviewed',
                'icon' => 'fas fa-user text-success'
            ],
            'job_match' => [
                'title' => 'New Job Match',
                'message' => 'We found a new job match for you: Data Analyst',
                'icon' => 'fas fa-briefcase text-primary'
            ],
            'profile_view' => [
                'title' => 'Profile Viewed',
                'message' => 'Your profile was viewed by TechCorp',
                'icon' => 'fas fa-eye text-info'
            ],
            'message' => [
                'title' => 'New Message',
                'message' => 'You have a new message from HR Manager',
                'icon' => 'fas fa-envelope text-warning'
            ],
            'system' => [
                'title' => 'System Notification',
                'message' => 'Your account has been verified successfully',
                'icon' => 'fas fa-bell text-secondary'
            ]
        ];
        
        // Create 5 random notifications for each user
        for ($i = 0; $i < 5; $i++) {
            $type = array_rand($notificationTypes);
            $notification = $notificationTypes[$type];
            
            Notification::create([
                'user_id' => $user->id,
                'title' => $notification['title'],
                'message' => $notification['message'],
                'type' => $type,
                'data' => [
                    'icon' => $notification['icon']
                ],
                'action_url' => $i % 2 === 0 ? route('notifications.index') : null,
                'read_at' => $i % 3 === 0 ? now() : null,
                'created_at' => now()->subHours(rand(1, 72))
            ]);
        }
    }
}