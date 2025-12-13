<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheClearJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-jobs {--all : Clear all cache, not just jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear job-related caches';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cacheService = app(\App\Services\CacheService::class);

        if ($this->option('all')) {
            $this->info('Clearing all cache...');
            $cacheService->flush();
            $this->info('✓ All cache cleared successfully!');
        } else {
            $this->info('Clearing job-related caches...');
            $cacheService->clearJobsCaches();
            $this->info('✓ Job caches cleared successfully!');
        }

        return Command::SUCCESS;
    }
}
