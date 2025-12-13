<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DeleteAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-all 
                            {--force : Force deletion without confirmation}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all users and their related data from the database';

    /**
     * Tables to clean up in order (respects foreign key constraints)
     */
    protected $tablesToClean = [
        'job_applications' => 'Job Applications',
        'saved_jobs' => 'Saved Jobs',
        'job_views' => 'Job Views',
        'jobs' => 'Job Postings',
        'messages' => 'Messages',
        'notifications' => 'Notifications',
        'employer_documents' => 'Employer Documents',
        'kyc_verifications' => 'KYC Verifications',
        'kyc_data' => 'KYC Data',
        'audit_logs' => 'Audit Logs',
        'employers' => 'Employer Profiles',
        'jobseekers' => 'Jobseeker Profiles',
        'password_reset_tokens' => 'Password Reset Tokens',
        'personal_access_tokens' => 'Personal Access Tokens',
        'users' => 'Users'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Delete All Users Command ===');
        $this->newLine();

        // Check if this is a dry run
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No data will be actually deleted');
            $this->newLine();
        }

        // Get current counts
        $counts = $this->getCurrentCounts();
        $totalUsers = $counts['users'] ?? 0;

        if ($totalUsers === 0) {
            $this->info('No users found in the database. Nothing to delete.');
            return Command::SUCCESS;
        }

        // Display what will be deleted
        $this->displayDeletionSummary($counts);

        // Confirmation (unless forced or dry run)
        if (!$this->option('force') && !$dryRun) {
            $this->newLine();
            $this->warn('WARNING: This action cannot be undone!');
            
            if (!$this->confirm('Are you sure you want to delete ALL users and related data?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            // Double confirmation for safety
            if (!$this->confirm('This will PERMANENTLY delete ALL user data. Are you absolutely sure?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        if ($dryRun) {
            $this->info('Dry run completed. No data was deleted.');
            return Command::SUCCESS;
        }

        // Perform the deletion
        return $this->performDeletion();
    }

    /**
     * Get current record counts for all tables
     */
    protected function getCurrentCounts(): array
    {
        $counts = [];
        
        foreach ($this->tablesToClean as $table => $description) {
            try {
                if (Schema::hasTable($table)) {
                    $counts[$table] = DB::table($table)->count();
                } else {
                    $counts[$table] = 0;
                }
            } catch (\Exception $e) {
                $counts[$table] = 0;
            }
        }

        return $counts;
    }

    /**
     * Display summary of what will be deleted
     */
    protected function displayDeletionSummary(array $counts): void
    {
        $this->info('The following data will be deleted:');
        $this->newLine();

        $totalRecords = 0;
        foreach ($this->tablesToClean as $table => $description) {
            $count = $counts[$table] ?? 0;
            $totalRecords += $count;
            
            if ($count > 0) {
                $this->line("  • {$description}: <comment>{$count} records</comment>");
            } else {
                $this->line("  • {$description}: <fg=gray>{$count} records</>");
            }
        }

        $this->newLine();
        $this->info("Total records to be deleted: <comment>{$totalRecords}</comment>");
    }

    /**
     * Perform the actual deletion
     */
    protected function performDeletion(): int
    {
        $this->newLine();
        $this->info('Starting deletion process...');
        
        try {
            DB::beginTransaction();

            $deletedCounts = [];
            $step = 1;

            // Delete data from each table
            foreach ($this->tablesToClean as $table => $description) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $this->info("{$step}. Deleting {$description}...");
                
                try {
                    $deleted = DB::table($table)->delete();
                    $deletedCounts[$table] = $deleted;
                    $this->line("   <info>Deleted {$deleted} records from {$table}</info>");
                } catch (\Exception $e) {
                    $this->error("   Failed to delete from {$table}: " . $e->getMessage());
                    throw $e;
                }
                
                $step++;
            }

            // Reset auto-increment counters
            $this->info("\nResetting auto-increment counters...");
            $this->resetAutoIncrements();

            DB::commit();

            $this->newLine();
            $this->info('=== SUCCESS ===');
            $this->info('All users and related data have been successfully deleted!');
            $this->info('Database is now clean and ready for new user registrations.');

            // Log the successful deletion
            Log::info('All users deleted via Artisan command', [
                'deleted_counts' => $deletedCounts,
                'total_tables_processed' => count($deletedCounts),
                'timestamp' => now(),
                'command' => 'users:delete-all'
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollback();
            
            $this->newLine();
            $this->error('=== ERROR ===');
            $this->error('An error occurred during deletion: ' . $e->getMessage());
            $this->error('All changes have been rolled back.');

            // Log the error
            Log::error('Error deleting all users via Artisan command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now(),
                'command' => 'users:delete-all'
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Reset auto-increment counters for all tables
     */
    protected function resetAutoIncrements(): void
    {
        foreach (array_keys($this->tablesToClean) as $table) {
            try {
                if (Schema::hasTable($table)) {
                    DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                    $this->line("  <info>Reset {$table} counter</info>");
                }
            } catch (\Exception $e) {
                $this->warn("  Warning: Could not reset {$table} counter: " . $e->getMessage());
            }
        }
    }
}
