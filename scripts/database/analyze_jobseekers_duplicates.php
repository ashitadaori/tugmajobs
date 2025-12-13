<?php

echo "==============================================\n";
echo "  JOBSEEKERS TABLE DUPLICATE ANALYSIS\n";
echo "==============================================\n\n";

class JobseekersDuplicateAnalyzer
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
        $this->showTableStructure();
        $this->showAllRecords();
        $this->findDuplicatesByEmail();
        $this->findDuplicatesByUserId();
        $this->findDuplicatesByName();
        $this->checkRelationshipConsistency();
        $this->generateCleanupRecommendations();
    }
    
    private function showTableStructure()
    {
        echo "📋 JOBSEEKERS TABLE STRUCTURE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            $stmt = $this->pdo->query("DESCRIBE `jobseekers`");
            $structure = $stmt->fetchAll();
            
            foreach ($structure as $column) {
                $key = $column['Key'] ? " ({$column['Key']})" : "";
                $null = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
                echo sprintf("  %-25s %-20s %s%s\n", 
                    $column['Field'], 
                    $column['Type'], 
                    $null,
                    $key
                );
            }
            echo "\n";
            
        } catch (PDOException $e) {
            echo "❌ Error getting table structure: " . $e->getMessage() . "\n";
        }
    }
    
    private function showAllRecords()
    {
        echo "📊 ALL JOBSEEKERS RECORDS:\n";
        echo str_repeat("-", 100) . "\n";
        
        try {
            $stmt = $this->pdo->query("SELECT * FROM `jobseekers` ORDER BY id");
            $records = $stmt->fetchAll();
            
            echo sprintf("%-4s %-8s %-25s %-30s %-15s %-20s\n", 
                'ID', 'User_ID', 'Name', 'Email', 'Mobile', 'Created_At'
            );
            echo str_repeat("-", 100) . "\n";
            
            foreach ($records as $record) {
                $name = strlen($record['name']) > 24 ? substr($record['name'], 0, 21) . '...' : $record['name'];
                $email = strlen($record['email']) > 29 ? substr($record['email'], 0, 26) . '...' : $record['email'];
                $mobile = $record['mobile'] ?? 'NULL';
                $created = $record['created_at'] ? date('Y-m-d H:i', strtotime($record['created_at'])) : 'NULL';
                
                echo sprintf("%-4s %-8s %-25s %-30s %-15s %-20s\n", 
                    $record['id'], 
                    $record['user_id'] ?? 'NULL',
                    $name,
                    $email,
                    $mobile,
                    $created
                );
            }
            echo "\nTotal records: " . count($records) . "\n\n";
            
        } catch (PDOException $e) {
            echo "❌ Error retrieving records: " . $e->getMessage() . "\n";
        }
    }
    
    private function findDuplicatesByEmail()
    {
        echo "🔍 CHECKING FOR EMAIL DUPLICATES:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            $stmt = $this->pdo->query("
                SELECT email, COUNT(*) as count, GROUP_CONCAT(id) as record_ids
                FROM `jobseekers` 
                WHERE email IS NOT NULL AND email != ''
                GROUP BY email 
                HAVING count > 1
                ORDER BY count DESC
            ");
            $duplicates = $stmt->fetchAll();
            
            if (empty($duplicates)) {
                echo "✅ No email duplicates found\n\n";
            } else {
                foreach ($duplicates as $dup) {
                    echo "📧 Email: {$dup['email']}\n";
                    echo "   Count: {$dup['count']} records\n";
                    echo "   IDs: {$dup['record_ids']}\n";
                    
                    // Show details for each duplicate
                    $ids = explode(',', $dup['record_ids']);
                    foreach ($ids as $id) {
                        $detailStmt = $this->pdo->prepare("SELECT * FROM `jobseekers` WHERE id = ?");
                        $detailStmt->execute([trim($id)]);
                        $detail = $detailStmt->fetch();
                        
                        echo "   → ID {$detail['id']}: User {$detail['user_id']}, Name: {$detail['name']}\n";
                    }
                    echo "\n";
                }
            }
            
        } catch (PDOException $e) {
            echo "❌ Error checking email duplicates: " . $e->getMessage() . "\n";
        }
    }
    
    private function findDuplicatesByUserId()
    {
        echo "🔍 CHECKING FOR USER_ID DUPLICATES:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            $stmt = $this->pdo->query("
                SELECT user_id, COUNT(*) as count, GROUP_CONCAT(id) as record_ids
                FROM `jobseekers` 
                WHERE user_id IS NOT NULL
                GROUP BY user_id 
                HAVING count > 1
                ORDER BY count DESC
            ");
            $duplicates = $stmt->fetchAll();
            
            if (empty($duplicates)) {
                echo "✅ No user_id duplicates found\n\n";
            } else {
                foreach ($duplicates as $dup) {
                    echo "👤 User ID: {$dup['user_id']}\n";
                    echo "   Count: {$dup['count']} records\n";
                    echo "   Jobseeker IDs: {$dup['record_ids']}\n";
                    
                    // Show details for each duplicate
                    $ids = explode(',', $dup['record_ids']);
                    foreach ($ids as $id) {
                        $detailStmt = $this->pdo->prepare("SELECT * FROM `jobseekers` WHERE id = ?");
                        $detailStmt->execute([trim($id)]);
                        $detail = $detailStmt->fetch();
                        
                        echo "   → Jobseeker ID {$detail['id']}: {$detail['name']} ({$detail['email']})\n";
                    }
                    echo "\n";
                }
            }
            
        } catch (PDOException $e) {
            echo "❌ Error checking user_id duplicates: " . $e->getMessage() . "\n";
        }
    }
    
    private function findDuplicatesByName()
    {
        echo "🔍 CHECKING FOR NAME DUPLICATES:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            $stmt = $this->pdo->query("
                SELECT name, COUNT(*) as count, GROUP_CONCAT(id) as record_ids
                FROM `jobseekers` 
                WHERE name IS NOT NULL AND name != ''
                GROUP BY name 
                HAVING count > 1
                ORDER BY count DESC
            ");
            $duplicates = $stmt->fetchAll();
            
            if (empty($duplicates)) {
                echo "✅ No name duplicates found\n\n";
            } else {
                foreach ($duplicates as $dup) {
                    echo "📝 Name: {$dup['name']}\n";
                    echo "   Count: {$dup['count']} records\n";
                    echo "   IDs: {$dup['record_ids']}\n";
                    
                    // Show details for each duplicate
                    $ids = explode(',', $dup['record_ids']);
                    foreach ($ids as $id) {
                        $detailStmt = $this->pdo->prepare("SELECT * FROM `jobseekers` WHERE id = ?");
                        $detailStmt->execute([trim($id)]);
                        $detail = $detailStmt->fetch();
                        
                        echo "   → ID {$detail['id']}: User {$detail['user_id']}, Email: {$detail['email']}\n";
                    }
                    echo "\n";
                }
            }
            
        } catch (PDOException $e) {
            echo "❌ Error checking name duplicates: " . $e->getMessage() . "\n";
        }
    }
    
    private function checkRelationshipConsistency()
    {
        echo "🔗 CHECKING RELATIONSHIP CONSISTENCY:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if all user_ids in jobseekers exist in users table
            $stmt = $this->pdo->query("
                SELECT j.id, j.user_id, j.name, j.email
                FROM `jobseekers` j
                LEFT JOIN `users` u ON j.user_id = u.id
                WHERE j.user_id IS NOT NULL AND u.id IS NULL
            ");
            $orphaned = $stmt->fetchAll();
            
            if (empty($orphaned)) {
                echo "✅ All jobseekers have valid user references\n";
            } else {
                echo "⚠️  Found jobseekers with invalid user_id references:\n";
                foreach ($orphaned as $record) {
                    echo "   → Jobseeker ID {$record['id']}: references non-existent user_id {$record['user_id']}\n";
                }
            }
            
            // Check for users without jobseeker profiles
            $stmt = $this->pdo->query("
                SELECT u.id, u.name, u.email, u.role
                FROM `users` u
                LEFT JOIN `jobseekers` j ON u.id = j.user_id
                WHERE u.role = 'jobseeker' AND j.id IS NULL
            ");
            $missingProfiles = $stmt->fetchAll();
            
            if (empty($missingProfiles)) {
                echo "✅ All jobseeker users have profiles\n";
            } else {
                echo "⚠️  Found users with role 'jobseeker' but no jobseeker profile:\n";
                foreach ($missingProfiles as $user) {
                    echo "   → User ID {$user['id']}: {$user['name']} ({$user['email']})\n";
                }
            }
            echo "\n";
            
        } catch (PDOException $e) {
            echo "❌ Error checking relationships: " . $e->getMessage() . "\n";
        }
    }
    
    private function generateCleanupRecommendations()
    {
        echo "💡 CLEANUP RECOMMENDATIONS:\n";
        echo str_repeat("=", 70) . "\n";
        
        try {
            // Get total counts
            $totalJobseekers = $this->pdo->query("SELECT COUNT(*) FROM jobseekers")->fetchColumn();
            $totalUsers = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'jobseeker'")->fetchColumn();
            
            echo "📊 SUMMARY:\n";
            echo "  • Total jobseeker records: $totalJobseekers\n";
            echo "  • Total users with 'jobseeker' role: $totalUsers\n\n";
            
            // Check for specific issues and provide recommendations
            $this->generateSpecificRecommendations();
            
        } catch (PDOException $e) {
            echo "❌ Error generating recommendations: " . $e->getMessage() . "\n";
        }
    }
    
    private function generateSpecificRecommendations()
    {
        echo "🛠️  SPECIFIC ACTIONS:\n\n";
        
        // Check for user_id duplicates
        $userIdDuplicates = $this->pdo->query("
            SELECT user_id, COUNT(*) as count 
            FROM jobseekers 
            WHERE user_id IS NOT NULL 
            GROUP BY user_id 
            HAVING count > 1
        ")->fetchAll();
        
        if (!empty($userIdDuplicates)) {
            echo "🔴 HIGH PRIORITY - User ID Duplicates:\n";
            foreach ($userIdDuplicates as $dup) {
                echo "   → User ID {$dup['user_id']} has {$dup['count']} jobseeker profiles\n";
            }
            echo "   📝 Action: Keep the most recent profile, merge data if needed\n\n";
        }
        
        // Check for email duplicates
        $emailDuplicates = $this->pdo->query("
            SELECT email, COUNT(*) as count 
            FROM jobseekers 
            WHERE email IS NOT NULL AND email != '' 
            GROUP BY email 
            HAVING count > 1
        ")->fetchAll();
        
        if (!empty($emailDuplicates)) {
            echo "🟡 MEDIUM PRIORITY - Email Duplicates:\n";
            foreach ($emailDuplicates as $dup) {
                echo "   → Email '{$dup['email']}' appears {$dup['count']} times\n";
            }
            echo "   📝 Action: Verify if these are legitimate separate accounts\n\n";
        }
        
        // Check for orphaned records
        $orphaned = $this->pdo->query("
            SELECT COUNT(*) FROM jobseekers j 
            LEFT JOIN users u ON j.user_id = u.id 
            WHERE j.user_id IS NOT NULL AND u.id IS NULL
        ")->fetchColumn();
        
        if ($orphaned > 0) {
            echo "🟠 LOW PRIORITY - Orphaned Records:\n";
            echo "   → $orphaned jobseeker records reference non-existent users\n";
            echo "   📝 Action: Clean up or reassign these records\n\n";
        }
        
        if (empty($userIdDuplicates) && empty($emailDuplicates) && $orphaned == 0) {
            echo "✅ GOOD NEWS - No Critical Issues Found!\n";
            echo "   Your jobseekers table appears to be clean and well-structured.\n\n";
        }
        
        echo "🔧 GENERATED CLEANUP SCRIPT:\n";
        echo "   A cleanup script will be created to help resolve any issues found.\n";
    }
}

// Run the analysis
$analyzer = new JobseekersDuplicateAnalyzer();
$analyzer->analyze();

echo str_repeat("=", 70) . "\n";
