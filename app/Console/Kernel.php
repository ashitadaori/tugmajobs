<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Re-analyze jobs that haven't been analyzed in 24 hours
        // This ensures job content analysis stays up-to-date
        $schedule->command('jobs:analyze-content --mode=stale --hours=24')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/job-analysis.log'));

        // Analyze any unanalyzed jobs every 6 hours
        // Catches any jobs that might have been missed
        $schedule->command('jobs:analyze-content --mode=unanalyzed')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
