<?php

echo "==============================================\n";
echo "  STANDALONE DATABASE CLEANUP TOOL\n";
echo "==============================================\n\n";

class StandaloneDatabaseCleanup
{
    private $pdo;
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
        $this->connectToDatabase();
    }

    private function connectToDatabase()
    {
        echo "🔗 Connecting to database...\n";
        
        // Read database config from .env
        $envFile = '.env';
        $config = [];
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $config[trim($key)] = trim($value);
                }
            }
        }

        $host = $config['DB_HOST'] ?? '127.0.0.1';
        $port = $config['DB_PORT'] ?? '3306';
        $database = $config['DB_DATABASE'] ?? 'job_portal';
        $username = $config['DB_USERNAME'] ?? 'root';
        $password = $config['DB_PASSWORD'] ?? '';

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            echo "✅ Database connection successful to: $database\n\n";
        } catch (PDOException $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            echo "Please ensure MySQL is running and database exists.\n";
            exit(1);
        }
    }

    private function loadActiveModels()
    {
        // Define currently used models and their expected tables
        $this->activeModels = [
            'users',
            'jobs',
            'job_applications',
            'categories',
            'job_types',
            'employer_profiles',
            'job_views',
            'notifications',
            'kyc_verifications',
            'kyc_data',
            'employers',
            'admins',
            'jobseekers',
            'employer_documents',
            'job_user', // pivot table for saved jobs
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
            'sessions'
        ];
    }

    public function analyze()
    {
        echo "🔍 Starting Database Analysis...\n\n";
        
        $this->analyzeTables();
        $this->findDuplicates();
        $this->generateReport();
        $this->generateCleanupScripts();
        
        return true;
    }

    private function analyzeTables()
    {
        echo "📊 Analyzing database tables...\n";
        
        try {
            $stmt = $this->pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $tableName) {
                $this->analyzeTable($tableName);
            }
            
            echo "   Found " . count($tables) . " tables\n\n";
            
        } catch (PDOException $e) {
            echo "❌ Error analyzing tables: " . $e->getMessage() . "\n";
        }
    }

    private function analyzeTable($tableName)
    {
        try {
            // Check if table has data
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
            $result = $stmt->fetch();
            $count = $result['count'];
            
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

        } catch (PDOException $e) {
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
        
        echo "   Found " . count($this->duplicateTables) . " duplicate patterns\n\n";
    }

    private function getTableBasePattern($tableName)
    {
        // Remove common suffixes/prefixes that might indicate duplicates
        $pattern = $tableName;
        
        // Remove trailing numbers
        $pattern = preg_replace('/_\d+$/', '', $pattern);
        
        // Remove backup suffixes
        $pattern = preg_replace('/_backup$|_old$|_temp$|_copy$/', '', $pattern);
        
        // Handle singular/plural variations
        if (substr($pattern, -1) === 's' && strlen($pattern) > 3) {
            $singular = substr($pattern, 0, -1);
            return $singular;
        }
        
        return $pattern;
    }

    private function generateReport()
    {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "                    DATABASE ANALYSIS REPORT\n";
        echo str_repeat("=", 70) . "\n\n";

        echo "📈 SUMMARY:\n";
        echo "  • Total tables with data: " . count($this->tablesWithData) . "\n";
        echo "  • Total empty tables: " . count($this->emptyTables) . "\n";
        echo "  • Potentially unused tables: " . count($this->unusedTables) . "\n";
        echo "  • Potential duplicate groups: " . count($this->duplicateTables) . "\n\n";

        if (!empty($this->tablesWithData)) {
            echo "📊 TABLES WITH DATA:\n";
            ksort($this->tablesWithData);
            foreach ($this->tablesWithData as $table => $count) {
                $status = in_array($table, $this->unusedTables) ? "🔴 UNUSED" : "✅ ACTIVE";
                echo sprintf("  • %-30s (%6d records) - %s\n", $table, $count, $status);
            }
            echo "\n";
        }

        if (!empty($this->emptyTables)) {
            echo "📭 EMPTY TABLES:\n";
            sort($this->emptyTables);
            foreach ($this->emptyTables as $table) {
                $status = in_array($table, $this->unusedTables) ? "🗑️  SAFE TO DROP" : "⚠️  SYSTEM TABLE";
                echo sprintf("  • %-30s - %s\n", $table, $status);
            }
            echo "\n";
        }

        if (!empty($this->duplicateTables)) {
            echo "👥 POTENTIAL DUPLICATES:\n";
            foreach ($this->duplicateTables as $pattern => $tables) {
                echo "  • Pattern '$pattern':\n";
                foreach ($tables as $table) {
                    $count = isset($this->tablesWithData[$table]) ? $this->tablesWithData[$table] : 0;
                    echo sprintf("    - %-28s (%6d records)\n", $table, $count);
                }
                echo "\n";
            }
        }

        if (!empty($this->unusedTables)) {
            echo "🗑️  UNUSED TABLES (candidates for removal):\n";
            sort($this->unusedTables);
            foreach ($this->unusedTables as $table) {
                $count = isset($this->tablesWithData[$table]) ? $this->tablesWithData[$table] : 0;
                $risk = $count > 0 ? "⚠️  HAS DATA" : "✅ EMPTY";
                echo sprintf("  • %-30s (%6d records) - %s\n", $table, $count, $risk);
            }
            echo "\n";
        }
    }

    private function generateCleanupScripts()
    {
        echo "🧹 GENERATING CLEANUP SCRIPTS...\n\n";
        
        $this->generateSqlScript();
        $this->generatePhpScript();
        $this->generateBackupScript();
    }

    private function generateSqlScript()
    {
        $cleanupSql = "-- GENERATED DATABASE CLEANUP SCRIPT\n";
        $cleanupSql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $cleanupSql .= "-- Database: " . $this->pdo->query("SELECT DATABASE()")->fetchColumn() . "\n\n";
        
        // Add backup recommendation
        $cleanupSql .= "-- IMPORTANT: CREATE BACKUP BEFORE RUNNING!\n";
        $cleanupSql .= "-- Run: mysqldump -u root -p job_portal > job_portal_backup_" . date('Y-m-d') . ".sql\n\n";
        
        // Safe to drop (empty unused tables)
        $safeToDrop = array_intersect($this->unusedTables, $this->emptyTables);
        if (!empty($safeToDrop)) {
            $cleanupSql .= "-- SAFE TO DROP (Empty unused tables)\n";
            $cleanupSql .= "-- These tables are empty and not used by the application\n";
            foreach ($safeToDrop as $table) {
                $cleanupSql .= "DROP TABLE IF EXISTS `$table`;\n";
            }
            $cleanupSql .= "\n";
        }
        
        // Review required (unused tables with data)
        $reviewRequired = array_diff($this->unusedTables, $this->emptyTables);
        if (!empty($reviewRequired)) {
            $cleanupSql .= "-- REVIEW REQUIRED (Unused tables with data)\n";
            $cleanupSql .= "-- These tables have data but seem unused - review before dropping\n";
            foreach ($reviewRequired as $table) {
                $count = $this->tablesWithData[$table] ?? 0;
                $cleanupSql .= "-- DROP TABLE IF EXISTS `$table`; -- Contains $count records - REVIEW FIRST!\n";
            }
            $cleanupSql .= "\n";
        }
        
        // Handle duplicates
        if (!empty($this->duplicateTables)) {
            $cleanupSql .= "-- POTENTIAL DUPLICATES\n";
            $cleanupSql .= "-- Review these groups and drop the unnecessary ones\n";
            foreach ($this->duplicateTables as $pattern => $tables) {
                $cleanupSql .= "-- Pattern: $pattern\n";
                foreach ($tables as $table) {
                    $count = $this->tablesWithData[$table] ?? 0;
                    $cleanupSql .= "-- DROP TABLE IF EXISTS `$table`; -- Contains $count records\n";
                }
                $cleanupSql .= "\n";
            }
        }
        
        file_put_contents('database_cleanup.sql', $cleanupSql);
        echo "✅ SQL cleanup script saved to: database_cleanup.sql\n";
    }

    private function generatePhpScript()
    {
        $safeToDrop = array_intersect($this->unusedTables, $this->emptyTables);
        
        if (empty($safeToDrop)) {
            echo "ℹ️  No safe tables to drop - PHP script not generated\n";
            return;
        }

        $phpScript = "<?php\n\n";
        $phpScript .= "// GENERATED SAFE DATABASE CLEANUP SCRIPT\n";
        $phpScript .= "// This script only drops empty, unused tables\n";
        $phpScript .= "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        $phpScript .= "echo \"Starting safe database cleanup...\\n\";\n\n";
        
        // Database connection
        $phpScript .= "try {\n";
        $phpScript .= "    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [\n";
        $phpScript .= "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION\n";
        $phpScript .= "    ]);\n";
        $phpScript .= "    echo \"✅ Database connected\\n\\n\";\n";
        $phpScript .= "} catch (PDOException \$e) {\n";
        $phpScript .= "    echo \"❌ Database connection failed: \" . \$e->getMessage() . \"\\n\";\n";
        $phpScript .= "    exit(1);\n";
        $phpScript .= "}\n\n";
        
        $phpScript .= "\$tablesToDrop = [\n";
        foreach ($safeToDrop as $table) {
            $phpScript .= "    '$table',\n";
        }
        $phpScript .= "];\n\n";
        
        $phpScript .= "foreach (\$tablesToDrop as \$table) {\n";
        $phpScript .= "    try {\n";
        $phpScript .= "        \$pdo->exec(\"DROP TABLE IF EXISTS `{\$table}`\");\n";
        $phpScript .= "        echo \"✅ Dropped table: \$table\\n\";\n";
        $phpScript .= "    } catch (PDOException \$e) {\n";
        $phpScript .= "        echo \"❌ Error dropping table \$table: \" . \$e->getMessage() . \"\\n\";\n";
        $phpScript .= "    }\n";
        $phpScript .= "}\n\n";
        
        $phpScript .= "echo \"\\n🎉 Safe cleanup completed!\\n\";\n";
        $phpScript .= "echo \"Dropped \" . count(\$tablesToDrop) . \" empty unused tables.\\n\";\n";
        
        file_put_contents('safe_database_cleanup.php', $phpScript);
        echo "✅ Safe PHP cleanup script saved to: safe_database_cleanup.php\n";
    }

    private function generateBackupScript()
    {
        $backupScript = "@echo off\n";
        $backupScript .= "REM Database backup script for Windows\n";
        $backupScript .= "REM Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        $backupScript .= "echo Creating database backup...\n";
        $backupScript .= "set BACKUP_FILE=job_portal_backup_%date:~-4,4%-%date:~-10,2%-%date:~-7,2%_%time:~0,2%-%time:~3,2%-%time:~6,2%.sql\n";
        $backupScript .= "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u root -p job_portal > %BACKUP_FILE%\n";
        $backupScript .= "echo Backup created: %BACKUP_FILE%\n";
        $backupScript .= "pause\n";
        
        file_put_contents('create_backup.bat', $backupScript);
        echo "✅ Backup script saved to: create_backup.bat\n\n";
    }
}

// Run the analyzer
try {
    $analyzer = new StandaloneDatabaseCleanup();
    if ($analyzer->analyze()) {
        echo "🎉 Analysis completed successfully!\n\n";
        echo "📋 NEXT STEPS:\n";
        echo "1. Review the analysis report above\n";
        echo "2. Create a backup by running: create_backup.bat\n";
        echo "3. For safe cleanup, run: php safe_database_cleanup.php\n";
        echo "4. For manual cleanup, review: database_cleanup.sql\n\n";
        echo "⚠️  IMPORTANT: Always backup before making changes!\n";
    }
} catch (Exception $e) {
    echo "❌ Analysis failed: " . $e->getMessage() . "\n";
    echo "Please ensure MySQL is running and accessible.\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
