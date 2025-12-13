<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear
                            {--channel= : Specific log channel to clear (all by default)}
                            {--days= : Clear logs older than X days}
                            {--force : Force clear without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application log files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $channel = $this->option('channel');
        $days = $this->option('days');
        $force = $this->option('force');

        if (!$force) {
            if (!$this->confirm('Are you sure you want to clear log files?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $logsPath = storage_path('logs');

        if ($channel) {
            $this->clearChannel($channel, $days);
        } else {
            $this->clearAllLogs($days);
        }

        $this->info('Log files cleared successfully!');

        return Command::SUCCESS;
    }

    /**
     * Clear specific channel logs
     *
     * @param string $channel
     * @param int|null $days
     * @return void
     */
    protected function clearChannel(string $channel, ?int $days): void
    {
        $logsPath = storage_path('logs');
        $pattern = $logsPath . '/' . $channel . '*.log';
        $files = glob($pattern);

        if (empty($files)) {
            $this->warn("No log files found for channel: {$channel}");
            return;
        }

        $clearedCount = 0;

        foreach ($files as $file) {
            if ($days) {
                $fileTime = filemtime($file);
                $cutoff = now()->subDays($days)->timestamp;

                if ($fileTime >= $cutoff) {
                    continue;
                }
            }

            if (File::delete($file)) {
                $clearedCount++;
                $this->line("Cleared: " . basename($file));
            }
        }

        $this->info("Cleared {$clearedCount} log file(s) for channel: {$channel}");
    }

    /**
     * Clear all logs
     *
     * @param int|null $days
     * @return void
     */
    protected function clearAllLogs(?int $days): void
    {
        $logsPath = storage_path('logs');
        $files = File::files($logsPath);

        $clearedCount = 0;

        foreach ($files as $file) {
            $filePath = $file->getPathname();

            // Skip .gitignore
            if (basename($filePath) === '.gitignore') {
                continue;
            }

            if ($days) {
                $fileTime = $file->getMTime();
                $cutoff = now()->subDays($days)->timestamp;

                if ($fileTime >= $cutoff) {
                    continue;
                }
            }

            if (File::delete($filePath)) {
                $clearedCount++;
                $this->line("Cleared: " . $file->getFilename());
            }
        }

        $this->info("Cleared {$clearedCount} log file(s)");
    }
}
