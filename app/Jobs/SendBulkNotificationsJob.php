<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 120;

    /**
     * User IDs to notify
     */
    protected array $userIds;

    /**
     * The notification class name
     */
    protected string $notificationClass;

    /**
     * The notification data
     */
    protected array $notificationData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $userIds, string $notificationClass, array $notificationData = [])
    {
        $this->userIds = $userIds;
        $this->notificationClass = $notificationClass;
        $this->notificationData = $notificationData;
        $this->onQueue('bulk-notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $successCount = 0;
        $failCount = 0;

        // Process in chunks to avoid memory issues
        $chunks = array_chunk($this->userIds, 100);

        foreach ($chunks as $chunk) {
            $users = User::whereIn('id', $chunk)->get();

            foreach ($users as $user) {
                try {
                    $notification = new $this->notificationClass(...array_values($this->notificationData));
                    $user->notify($notification);
                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                    Log::channel('jobs')->warning('Failed to send bulk notification to user', [
                        'user_id' => $user->id,
                        'notification_type' => $this->notificationClass,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Small delay between chunks to prevent overwhelming the system
            usleep(100000); // 100ms
        }

        Log::channel('jobs')->info('Bulk notification job completed', [
            'notification_type' => $this->notificationClass,
            'total_users' => count($this->userIds),
            'success_count' => $successCount,
            'fail_count' => $failCount,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('jobs')->error('Bulk notification job failed', [
            'notification_type' => $this->notificationClass,
            'total_users' => count($this->userIds),
            'error' => $exception->getMessage(),
        ]);
    }
}
