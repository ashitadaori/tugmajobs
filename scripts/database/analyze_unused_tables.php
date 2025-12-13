<?php

echo "==============================================\n";
echo "  DETAILED ANALYSIS OF UNUSED TABLES\n";
echo "==============================================\n\n";

class UnusedTableAnalyzer
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
    
    public function analyze()
    {
        $unusedTablesWithData = [
            'company_sizes',
            'industries',
            'job_application_status_histories',
            'job_categories',
            'job_skills',
            'locations',
            'saved_jobs'
        ];
        
        foreach ($unusedTablesWithData as $table) {
            $this->analyzeTable($table);
        }
        
        $this->generateRecommendations();
    }
    
    private function analyzeTable($tableName)
    {
        echo str_repeat("=", 50) . "\n";
        echo "ANALYZING TABLE: $tableName\n";
        echo str_repeat("=", 50) . "\n";
        
        try {
            // Get table structure
            $stmt = $this->pdo->query("DESCRIBE `$tableName`");
            $structure = $stmt->fetchAll();
            
            echo "📋 TABLE STRUCTURE:\n";
            foreach ($structure as $column) {
                echo "  • {$column['Field']} ({$column['Type']}) - {$column['Key']}\n";
            }
            echo "\n";
            
            // Get row count
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
            $count = $stmt->fetch()['count'];
            echo "📊 RECORD COUNT: $count\n\n";
            
            // Show sample data (first 3 rows)
            $stmt = $this->pdo->query("SELECT * FROM `$tableName` LIMIT 3");
            $sampleData = $stmt->fetchAll();
            
            if (!empty($sampleData)) {
                echo "📝 SAMPLE DATA:\n";
                foreach ($sampleData as $i => $row) {
                    echo "  Row " . ($i + 1) . ":\n";
                    foreach ($row as $key => $value) {
                        $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                        echo "    $key: $displayValue\n";
                    }
                    echo "\n";
                }
            }
            
            // Check for foreign key relationships
            $this->checkForeignKeyRelationships($tableName);
            
        } catch (PDOException $e) {
            echo "❌ Error analyzing table $tableName: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkForeignKeyRelationships($tableName)
    {
        try {
            // Check if this table is referenced by other tables
            $stmt = $this->pdo->query("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE REFERENCED_TABLE_NAME = '$tableName' 
                AND REFERENCED_TABLE_SCHEMA = 'job_portal'
            ");
            $references = $stmt->fetchAll();
            
            if (!empty($references)) {
                echo "🔗 REFERENCED BY:\n";
                foreach ($references as $ref) {
                    echo "  • {$ref['TABLE_NAME']}.{$ref['COLUMN_NAME']} -> {$ref['REFERENCED_TABLE_NAME']}.{$ref['REFERENCED_COLUMN_NAME']}\n";
                }
                echo "\n";
            }
            
            // Check if this table references other tables
            $stmt = $this->pdo->query("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = '$tableName' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
                AND TABLE_SCHEMA = 'job_portal'
            ");
            $foreignKeys = $stmt->fetchAll();
            
            if (!empty($foreignKeys)) {
                echo "🔗 REFERENCES:\n";
                foreach ($foreignKeys as $fk) {
                    echo "  • {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
                }
                echo "\n";
            }
            
            if (empty($references) && empty($foreignKeys)) {
                echo "🔗 RELATIONSHIPS: None found\n\n";
            }
            
        } catch (PDOException $e) {
            echo "⚠️  Could not check relationships: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function generateRecommendations()
    {
        echo str_repeat("=", 70) . "\n";
        echo "                    RECOMMENDATIONS\n";
        echo str_repeat("=", 70) . "\n\n";
        
        echo "📋 ANALYSIS SUMMARY:\n\n";
        
        echo "🔴 HIGH PRIORITY - SAFE TO DROP:\n";
        echo "  • password_resets - Already dropped (was empty)\n\n";
        
        echo "🟡 MEDIUM PRIORITY - REVIEW DATA FIRST:\n";
        echo "  • job_application_status_histories - Seems like old audit/history data\n";
        echo "  • saved_jobs - Might be replaced by job_user pivot table\n\n";
        
        echo "🟠 LOW PRIORITY - MIGHT BE NEEDED:\n";
        echo "  • company_sizes - Could be reference data for forms\n";
        echo "  • industries - Could be reference data for forms\n";
        echo "  • job_categories - Might be old category system\n";
        echo "  • job_skills - Could be skills reference data\n";
        echo "  • locations - Could be location reference data\n\n";
        
        echo "💡 RECOMMENDED ACTIONS:\n\n";
        echo "1. BACKUP FIRST: Run create_backup.bat\n\n";
        echo "2. CHECK CODE REFERENCES:\n";
        echo "   Search your codebase for references to these tables:\n";
        echo "   - grep -r \"company_sizes\" app/\n";
        echo "   - grep -r \"industries\" app/\n";
        echo "   - grep -r \"job_categories\" app/\n";
        echo "   - etc.\n\n";
        
        echo "3. GRADUAL CLEANUP:\n";
        echo "   Start with tables that have no foreign key relationships\n";
        echo "   and are not referenced in your code.\n\n";
        
        echo "4. DATA MIGRATION:\n";
        echo "   If you need some data from these tables, migrate it to\n";
        echo "   your current table structure before dropping.\n\n";
        
        echo "⚠️  IMPORTANT NOTES:\n";
        echo "• Always test on a development copy first\n";
        echo "• Some tables might be used by old features you forgot about\n";
        echo "• Check if any tables contain user-generated data that should be preserved\n";
        echo "• Consider renaming tables instead of dropping (add '_deprecated' suffix)\n\n";
    }
}

// Run the analyzer
$analyzer = new UnusedTableAnalyzer();
$analyzer->analyze();

echo "🎉 Detailed analysis completed!\n";
echo str_repeat("=", 70) . "\n";
