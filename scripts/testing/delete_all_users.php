<?php
/**
 * Delete All Users Script for Job Portal
 * 
 * This script will delete all users and their related data from the database.
 * Use with caution - this action cannot be undone!
 * 
 * Usage: php delete_all_users.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== Delete All Users Script ===\n\n";

// Confirmation prompt
echo "WARNING: This will permanently delete ALL users and their related data!\n";
echo "This includes:\n";
echo "- All users from 'users' table\n";
echo "- All jobseeker profiles\n";
echo "- All employer profiles\n";
echo "- All admin profiles\n";
echo "- All job applications\n";
echo "- All jobs posted by employers\n";
echo "- All KYC verification data\n";
echo "- All notifications\n";
echo "- All employer documents\n";
echo "- All saved jobs\n";
echo "- All messages\n";
echo "- All audit logs\n\n";

echo "Type 'DELETE_ALL_USERS' to confirm: ";
$confirmation = trim(fgets(STDIN));

if ($confirmation !== 'DELETE_ALL_USERS') {
    echo "Operation cancelled.\n";
    exit(0);
}

echo "\nStarting deletion process...\n\n";

try {
    // Start transaction
    DB::beginTransaction();

    // Get count before deletion
    $userCount = DB::table('users')->count();
    echo "Found {$userCount} users to delete.\n\n";

    if ($userCount === 0) {
        echo "No users found. Nothing to delete.\n";
        DB::rollback();
        exit(0);
    }

    // Delete in proper order to handle foreign key constraints
    
    echo "1. Deleting job applications...\n";
    $deletedApplications = DB::table('job_applications')->delete();
    echo "   Deleted {$deletedApplications} job applications.\n";

    echo "2. Deleting saved jobs...\n";
    $deletedSavedJobs = DB::table('saved_jobs')->delete();
    echo "   Deleted {$deletedSavedJobs} saved job records.\n";

    echo "3. Deleting job views...\n";
    $deletedJobViews = DB::table('job_views')->delete();
    echo "   Deleted {$deletedJobViews} job view records.\n";

    echo "4. Deleting jobs...\n";
    $deletedJobs = DB::table('jobs')->delete();
    echo "   Deleted {$deletedJobs} job postings.\n";

    echo "5. Deleting messages...\n";
    $deletedMessages = DB::table('messages')->delete();
    echo "   Deleted {$deletedMessages} messages.\n";

    echo "6. Deleting notifications...\n";
    $deletedNotifications = DB::table('notifications')->delete();
    echo "   Deleted {$deletedNotifications} notifications.\n";

    echo "7. Deleting employer documents...\n";
    $deletedDocuments = DB::table('employer_documents')->delete();
    echo "   Deleted {$deletedDocuments} employer documents.\n";

    echo "8. Deleting KYC verification data...\n";
    $deletedKycVerifications = DB::table('kyc_verifications')->delete();
    echo "   Deleted {$deletedKycVerifications} KYC verification records.\n";

    echo "9. Deleting KYC data...\n";
    $deletedKycData = DB::table('kyc_data')->delete();
    echo "   Deleted {$deletedKycData} KYC data records.\n";

    echo "10. Deleting audit logs...\n";
    $deletedAuditLogs = DB::table('audit_logs')->delete();
    echo "    Deleted {$deletedAuditLogs} audit log entries.\n";

    echo "11. Deleting employer profiles...\n";
    $deletedEmployers = DB::table('employers')->delete();
    echo "    Deleted {$deletedEmployers} employer profiles.\n";

    echo "12. Deleting jobseeker profiles...\n";
    $deletedJobseekers = DB::table('jobseekers')->delete();
    echo "    Deleted {$deletedJobseekers} jobseeker profiles.\n";

    echo "13. Deleting admin profiles...\n";
    $deletedAdmins = DB::table('admins')->delete();
    echo "    Deleted {$deletedAdmins} admin profiles.\n";

    echo "14. Deleting password reset tokens...\n";
    $deletedTokens = DB::table('password_reset_tokens')->delete();
    echo "    Deleted {$deletedTokens} password reset tokens.\n";

    echo "15. Deleting personal access tokens...\n";
    $deletedAccessTokens = DB::table('personal_access_tokens')->delete();
    echo "    Deleted {$deletedAccessTokens} personal access tokens.\n";

    echo "16. Finally, deleting all users...\n";
    $deletedUsers = DB::table('users')->delete();
    echo "    Deleted {$deletedUsers} users.\n";

    // Reset auto-increment counters
    echo "\n17. Resetting auto-increment counters...\n";
    $tables = [
        'users',
        'jobseekers', 
        'employers',
        'admins',
        'jobs',
        'job_applications',
        'saved_jobs',
        'job_views',
        'messages',
        'notifications',
        'employer_documents',
        'kyc_verifications',
        'kyc_data',
        'audit_logs',
        'password_reset_tokens',
        'personal_access_tokens'
    ];

    foreach ($tables as $table) {
        try {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                echo "    Reset {$table} counter.\n";
            }
        } catch (Exception $e) {
            echo "    Warning: Could not reset {$table} counter: " . $e->getMessage() . "\n";
        }
    }

    // Commit transaction
    DB::commit();
    
    echo "\n=== SUCCESS ===\n";
    echo "All users and related data have been successfully deleted!\n";
    echo "Database is now clean and ready for new user registrations.\n";
    
    // Log the action
    Log::info('All users deleted via delete_all_users.php script', [
        'deleted_users' => $deletedUsers,
        'timestamp' => now(),
        'tables_cleared' => count($tables)
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    DB::rollback();
    
    echo "\n=== ERROR ===\n";
    echo "An error occurred during deletion: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
    
    // Log the error
    Log::error('Error deleting all users', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => now()
    ]);
    
    exit(1);
}

echo "\nScript completed successfully.\n";
?>
