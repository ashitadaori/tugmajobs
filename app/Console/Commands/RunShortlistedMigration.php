<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class RunShortlistedMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-shortlisted-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the migration to add the shortlisted column to job_applications table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking if shortlisted column exists in job_applications table...');
        
        if (Schema::hasColumn('job_applications', 'shortlisted')) {
            $this->info('The shortlisted column already exists in the job_applications table.');
            return;
        }
        
        $this->info('Running migration to add shortlisted column to job_applications table...');
        
        // Run the specific migration
        $exitCode = Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_07_21_000000_add_shortlisted_to_job_applications.php',
            '--force' => true,
        ]);
        
        if ($exitCode === 0) {
            $this->info('Migration completed successfully!');
        } else {
            $this->error('Migration failed. Please run the migration manually: php artisan migrate');
        }
    }
}