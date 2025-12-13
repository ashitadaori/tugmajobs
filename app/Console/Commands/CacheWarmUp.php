<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheWarmUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application cache by pre-loading commonly accessed data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Warming up cache...');

        $jobRepo = app(\App\Repositories\JobRepository::class);
        $categoryRepo = app(\App\Repositories\CategoryRepository::class);
        $jobTypeRepo = app(\App\Repositories\JobTypeRepository::class);

        // Warm up categories cache
        $this->info('→ Loading categories...');
        $categories = $categoryRepo->getAllActive();
        $this->line("  Cached {$categories->count()} categories");

        // Warm up job types cache
        $this->info('→ Loading job types...');
        $jobTypes = $jobTypeRepo->getAllActive();
        $this->line("  Cached {$jobTypes->count()} job types");

        // Warm up featured jobs cache
        $this->info('→ Loading featured jobs...');
        $featuredJobs = $jobRepo->getFeaturedJobs();
        $this->line("  Cached {$featuredJobs->count()} featured jobs");

        // Warm up recent jobs cache
        $this->info('→ Loading recent jobs...');
        $recentJobs = $jobRepo->getRecentJobs();
        $this->line("  Cached {$recentJobs->count()} recent jobs");

        // Warm up job statistics
        $this->info('→ Loading job statistics...');
        $stats = $jobRepo->getStatistics();
        $this->line("  Cached statistics");

        $this->newLine();
        $this->info('✓ Cache warmed up successfully!');

        return Command::SUCCESS;
    }
}
