<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
try {
    $app = require_once 'bootstrap/app.php';
    if ($app) {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
} catch (Exception $e) {
    echo "Laravel bootstrap warning: " . $e->getMessage() . "\n";
    // Continue anyway, we'll handle DB connection directly
}

echo "==============================================\n";
echo "  DATABASE CLEANUP & ANALYSIS TOOL\n";
echo "==============================================\n\n";

class DatabaseCleanupAnalyzer
{
    private $activeModels = [];
    private $migrationTables = [];
    private $unusedTables = [];
    private $duplicateTables = [];
    private $tablesWithData = [];
    private $emptyTables = [];

    public function __construct()
    {
        $this->loadActiveModels();
        $this->loadMigrationTables();
    }

    public function analyze()
    {
        echo "🔍 Starting Database Analysis...\n\n";
        
        try {
            // Test database connection
            DB::connection()->getPdo();
            echo "✅ Database connection successful\n\n";
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            echo "Please ensure MySQL is running and database exists.\n";
            return false;
        }

        $this->analyzeTables();
        $this->findDuplicates();
        $this->generateReport();
        $this->generateCleanupScript();

        return true;
    }

    private function loadActiveModels()
    {
        // Define currently used models and their expected tables
        $this->activeModels = [
            'User' => 'users',
            'Job' => 'jobs',
            'JobApplication' => 'job_applications',
            'Category' => 'categories',
            'JobType' => 'job_types',
            'EmployerProfile' => 'employer_profiles',
            'JobView' => 'job_views',
            'Notification' => 'notifications',
            'KycVerification' => 'kyc_verifications',
            'KycData' => 'kyc_data',
            'Employer' => 'employers',
            'Admin' => 'admins',
            'Jobseeker' => 'jobseekers',
            'EmployerDocument' => 'employer_documents',
            'SavedJob' => 'job_user', // pivot table
        ];
    }

    private function loadMigrationTables()
    {
        // System tables that should be kept
        $this->migrationTables = [
            'migrations',
            'failed_jobs',
            'password_reset_tokens',
            'personal_access_tokens',
            'cache',
            'cache_locks',
            'jobs',
            'sessions'
        ];
    }

    private function analyzeTables()
    {
        echo "📊 Analyzing database tables...\n";
        
        try {
            $tables = DB::select('SHOW TABLES');
            $dbName = DB::getDatabaseName();
            
            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_$dbName"};
                $this->analyzeTable($tableName);
            }
            
        } catch (Exception $e) {
            echo "❌ Error analyzing tables: " . $e->getMessage() . "\n";
        }
    }

    private function analyzeTable($tableName)
    {
        try {
            // Check if table has data
            $count = DB::table($tableName)->count();
            
            if ($count > 0) {
                $this->tablesWithData[$tableName] = $count;
            } else {
                $this->emptyTables[] = $tableName;
            }

            // Check if table is used by active models or is system table
            $isActive = in_array($tableName, $this->activeModels) || 
                       in_array($tableName, $this->migrationTables);
            
            if (!$isActive) {
                $this->unusedTables[] = $tableName;
            }

        } catch (Exception $e) {
            echo "  ⚠️  Error analyzing table $tableName: " . $e->getMessage() . "\n";
        }
    }

    private function findDuplicates()
    {
        echo "🔍 Searching for duplicate table patterns...\n";
        
        $allTables = array_merge(array_keys($this->tablesWithData), $this->emptyTables);
        
        // Group similar table names
        $groups = [];
        foreach ($allTables as $table) {
            $basePattern = $this->getTableBasePattern($table);
            $groups[$basePattern][] = $table;
        }

        // Find groups with multiple tables (potential duplicates)
        foreach ($groups as $pattern => $tables) {
            if (count($tables) > 1) {
                $this->duplicateTables[$pattern] = $tables;
            }
        }
    }

    private function getTableBasePattern($tableName)
    {
        // Remove common suffixes/prefixes that might indicate duplicates
        $pattern = $tableName;
        
        // Remove trailing numbers
        $pattern = preg_replace('/_\d+$/', '', $pattern);
        
        // Remove backup suffixes
        $pattern = preg_replace('/_backup$|_old$|_temp$/', '', $pattern);
        
        // Handle singular/plural variations
        if (substr($pattern, -1) === 's') {
            $singular = substr($pattern, 0, -1);
            return $singular;
        }
        
        return $pattern;
    }

    private function generateReport()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "                    ANALYSIS REPORT\n";
        echo str_repeat("=", 60) . "\n\n";

        echo "📈 SUMMARY:\n";
        echo "  • Total tables with data: " . count($this->tablesWithData) . "\n";
        echo "  • Total empty tables: " . count($this->emptyTables) . "\n";
        echo "  • Potentially unused tables: " . count($this->unusedTables) . "\n";
        echo "  • Potential duplicate groups: " . count($this->duplicateTables) . "\n\n";

        if (!empty($this->tablesWithData)) {
            echo "📊 TABLES WITH DATA:\n";
            foreach ($this->tablesWithData as $table => $count) {
                $status = in_array($table, $this->unusedTables) ? "🔴 UNUSED" : "✅ ACTIVE";
                echo "  • $table ($count records) - $status\n";
            }
            echo "\n";
        }

        if (!empty($this->emptyTables)) {
            echo "📭 EMPTY TABLES (safe to remove):\n";
            foreach ($this->emptyTables as $table) {
                $status = in_array($table, $this->unusedTables) ? "🗑️  SAFE TO DROP" : "⚠️  SYSTEM TABLE";
                echo "  • $table - $status\n";
            }
            echo "\n";
        }

        if (!empty($this->duplicateTables)) {
            echo "👥 POTENTIAL DUPLICATES:\n";
            foreach ($this->duplicateTables as $pattern => $tables) {
                echo "  • Pattern '$pattern':\n";
                foreach ($tables as $table) {
                    $count = isset($this->tablesWithData[$table]) ? $this->tablesWithData[$table] : 0;
                    echo "    - $table ($count records)\n";
                }
                echo "\n";
            }
        }

        if (!empty($this->unusedTables)) {
            echo "🗑️  UNUSED TABLES (candidates for removal):\n";
            foreach ($this->unusedTables as $table) {
                $count = isset($this->tablesWithData[$table]) ? $this->tablesWithData[$table] : 0;
                $risk = $count > 0 ? "⚠️  HAS DATA" : "✅ EMPTY";
                echo "  • $table ($count records) - $risk\n";
            }
            echo "\n";
        }
    }

    private function generateCleanupScript()
    {
        echo "🧹 GENERATING CLEANUP SCRIPT...\n\n";
        
        $cleanupSql = "-- GENERATED DATABASE CLEANUP SCRIPT\n";
        $cleanupSql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Add backup recommendation
        $cleanupSql .= "-- IMPORTANT: CREATE BACKUP BEFORE RUNNING!\n";
        $cleanupSql .= "-- mysqldump -u root -p job_portal > job_portal_backup_" . date('Y-m-d') . ".sql\n\n";
        
        // Safe to drop (empty unused tables)
        $safeToDrop = array_intersect($this->unusedTables, $this->emptyTables);
        if (!empty($safeToDrop)) {
            $cleanupSql .= "-- SAFE TO DROP (Empty unused tables)\n";
            foreach ($safeToDrop as $table) {
                $cleanupSql .= "DROP TABLE IF EXISTS `$table`;\n";
            }
            $cleanupSql .= "\n";
        }
        
        // Review required (unused tables with data)
        $reviewRequired = array_diff($this->unusedTables, $this->emptyTables);
        if (!empty($reviewRequired)) {
            $cleanupSql .= "-- REVIEW REQUIRED (Unused tables with data - uncomment after review)\n";
            foreach ($reviewRequired as $table) {
                $count = $this->tablesWithData[$table] ?? 0;
                $cleanupSql .= "-- DROP TABLE IF EXISTS `$table`; -- Contains $count records\n";
            }
            $cleanupSql .= "\n";
        }
        
        // Handle duplicates
        if (!empty($this->duplicateTables)) {
            $cleanupSql .= "-- POTENTIAL DUPLICATES (Review and uncomment appropriate ones)\n";
            foreach ($this->duplicateTables as $pattern => $tables) {
                $cleanupSql .= "-- Pattern: $pattern\n";
                foreach ($tables as $table) {
                    $count = $this->tablesWithData[$table] ?? 0;
                    $cleanupSql .= "-- DROP TABLE IF EXISTS `$table`; -- Contains $count records\n";
                }
                $cleanupSql .= "\n";
            }
        }
        
        // Write cleanup script to file
        file_put_contents('database_cleanup.sql', $cleanupSql);
        echo "✅ Cleanup script saved to: database_cleanup.sql\n\n";
        
        // Generate PHP cleanup script
        $this->generatePhpCleanupScript($safeToDrop);
    }

    private function generatePhpCleanupScript($safeToDrop)
    {
        $phpScript = "<?php\n\n";
        $phpScript .= "// GENERATED SAFE DATABASE CLEANUP SCRIPT\n";
        $phpScript .= "// This script only drops empty, unused tables\n\n";
        
        $phpScript .= "require_once 'vendor/autoload.php';\n\n";
        $phpScript .= "\$app = require_once 'bootstrap/app.php';\n";
        $phpScript .= "\$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n";
        $phpScript .= "\$kernel->bootstrap();\n\n";
        
        $phpScript .= "echo \"Starting safe database cleanup...\\n\";\n\n";
        
        if (!empty($safeToDrop)) {
            $phpScript .= "\$tablesToDrop = [\n";
            foreach ($safeToDrop as $table) {
                $phpScript .= "    '$table',\n";
            }
            $phpScript .= "];\n\n";
            
            $phpScript .= "foreach (\$tablesToDrop as \$table) {\n";
            $phpScript .= "    try {\n";
            $phpScript .= "        DB::statement(\"DROP TABLE IF EXISTS `{\$table}`\");\n";
            $phpScript .= "        echo \"✅ Dropped table: \$table\\n\";\n";
            $phpScript .= "    } catch (Exception \$e) {\n";
            $phpScript .= "        echo \"❌ Error dropping table \$table: \" . \$e->getMessage() . \"\\n\";\n";
            $phpScript .= "    }\n";
            $phpScript .= "}\n\n";
        }
        
        $phpScript .= "echo \"\\nCleanup completed!\\n\";\n";
        
        file_put_contents('safe_database_cleanup.php', $phpScript);
        echo "✅ Safe PHP cleanup script saved to: safe_database_cleanup.php\n";
    }
}

// Run the analyzer
$analyzer = new DatabaseCleanupAnalyzer();
if ($analyzer->analyze()) {
    echo "🎉 Analysis completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Review the analysis above\n";
    echo "2. Create a backup: mysqldump -u root -p job_portal > backup.sql\n";
    echo "3. Run: php safe_database_cleanup.php (for safe cleanup)\n";
    echo "4. Review database_cleanup.sql for manual cleanup options\n";
} else {
    echo "❌ Analysis failed due to database connection issues.\n";
    echo "Please start MySQL service and ensure the database exists.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
