<?php

echo "==============================================\n";
echo "  CONSERVATIVE DATABASE CLEANUP\n";
echo "==============================================\n\n";

class ConservativeDatabaseCleanup
{
    private $pdo;
    
    public function __construct()
    {
        $this->connectToDatabase();
    }
    
    private function connectToDatabase()
    {
        try {
            $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            echo "✅ Connected to database\n\n";
        } catch (PDOException $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function cleanup()
    {
        echo "🧹 Starting conservative cleanup...\n\n";
        
        // These tables are actively used and should NOT be touched
        $activeTables = [
            'saved_jobs', // Used by SavedJob model and User relationships
            'job_application_status_histories' // Used by ApplicationStatusHistory model
        ];
        
        // These tables contain reference data that might be needed later
        $referenceDataTables = [
            'company_sizes',
            'industries', 
            'job_categories',
            'job_skills',
            'locations'
        ];
        
        echo "ℹ️  KEEPING ACTIVE TABLES:\n";
        foreach ($activeTables as $table) {
            echo "  • $table - ACTIVE (has model and relationships)\n";
        }
        echo "\n";
        
        echo "🏷️  MARKING REFERENCE TABLES AS DEPRECATED:\n";
        foreach ($referenceDataTables as $table) {
            $this->renameToDeprecated($table);
        }
        
        echo "\n✅ Conservative cleanup completed!\n\n";
        
        $this->showSummary();
    }
    
    private function renameToDeprecated($tableName)
    {
        try {
            $newTableName = $tableName . '_deprecated_' . date('Y_m_d');
            
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE '$tableName'");
            if ($stmt->rowCount() == 0) {
                echo "  • $tableName - Table doesn't exist, skipping\n";
                return;
            }
            
            // Check if deprecated version already exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE '$newTableName'");
            if ($stmt->rowCount() > 0) {
                echo "  • $tableName - Already deprecated as $newTableName\n";
                return;
            }
            
            // Rename the table
            $this->pdo->exec("RENAME TABLE `$tableName` TO `$newTableName`");
            echo "  • $tableName → $newTableName (preserved as deprecated)\n";
            
        } catch (PDOException $e) {
            echo "  • ❌ Error renaming $tableName: " . $e->getMessage() . "\n";
        }
    }
    
    private function showSummary()
    {
        echo "📋 CLEANUP SUMMARY:\n\n";
        
        echo "✅ PRESERVED TABLES:\n";
        echo "  • saved_jobs - Active model with relationships\n";
        echo "  • job_application_status_histories - Active model for audit trail\n\n";
        
        echo "🏷️  DEPRECATED TABLES:\n";
        echo "  • Reference data tables renamed with '_deprecated_" . date('Y_m_d') . "' suffix\n";
        echo "  • Can be restored if needed: RENAME TABLE back to original name\n\n";
        
        echo "💡 BENEFITS OF THIS APPROACH:\n";
        echo "  • No data loss - everything is preserved\n";
        echo "  • Clean active database structure\n";
        echo "  • Easy rollback if tables are needed\n";
        echo "  • Clear separation between active and unused tables\n\n";
        
        echo "🔄 TO RESTORE A DEPRECATED TABLE:\n";
        echo "  RENAME TABLE `tablename_deprecated_" . date('Y_m_d') . "` TO `tablename`;\n\n";
        
        echo "🗑️  TO PERMANENTLY DELETE DEPRECATED TABLES (after confirming not needed):\n";
        echo "  DROP TABLE `tablename_deprecated_" . date('Y_m_d') . "`;\n\n";
    }
}

// Confirm with user before proceeding
echo "This script will:\n";
echo "1. Keep 'saved_jobs' and 'job_application_status_histories' (they are actively used)\n";
echo "2. Rename reference data tables to '_deprecated_" . date('Y_m_d') . "' suffix\n";
echo "3. Preserve all data for potential future use\n\n";

echo "Do you want to proceed? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) === 'y' || trim(strtolower($line)) === 'yes') {
    $cleanup = new ConservativeDatabaseCleanup();
    $cleanup->cleanup();
} else {
    echo "Cleanup cancelled by user.\n";
}

echo str_repeat("=", 50) . "\n";
