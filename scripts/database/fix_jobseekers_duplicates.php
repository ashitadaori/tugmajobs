<?php

echo "==============================================\n";
echo "  JOBSEEKERS DUPLICATE ANALYSIS & FIX\n";
echo "==============================================\n\n";

class JobseekersDuplicateFixer
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
    
    public function analyzeAndFix()
    {
        $this->showCurrentState();
        $this->identifyDuplicates();
        $this->showUserDetails();
        $this->generateFixScript();
    }
    
    private function showCurrentState()
    {
        echo "📊 CURRENT JOBSEEKERS TABLE STATE:\n";
        echo str_repeat("-", 80) . "\n";
        
        try {
            $stmt = $this->pdo->query("
                SELECT j.id, j.user_id, 
                       CONCAT(COALESCE(j.first_name, ''), ' ', COALESCE(j.last_name, '')) as full_name,
                       u.name as user_name, u.email, u.role,
                       j.created_at, j.updated_at
                FROM jobseekers j
                LEFT JOIN users u ON j.user_id = u.id
                ORDER BY j.user_id, j.created_at
            ");
            $records = $stmt->fetchAll();
            
            echo sprintf("%-4s %-8s %-25s %-25s %-30s %-20s\n", 
                'JS_ID', 'User_ID', 'Jobseeker_Name', 'User_Name', 'Email', 'Created'
            );
            echo str_repeat("-", 120) . "\n";
            
            foreach ($records as $record) {
                $jsName = trim($record['full_name']) ?: 'N/A';
                $userName = $record['user_name'] ?: 'N/A';
                $email = $record['email'] ?: 'N/A';
                $created = $record['created_at'] ? date('Y-m-d H:i', strtotime($record['created_at'])) : 'N/A';
                
                echo sprintf("%-4s %-8s %-25s %-25s %-30s %-20s\n", 
                    $record['id'], 
                    $record['user_id'],
                    substr($jsName, 0, 24),
                    substr($userName, 0, 24),
                    substr($email, 0, 29),
                    $created
                );
            }
            echo "\nTotal jobseeker records: " . count($records) . "\n\n";
            
        } catch (PDOException $e) {
            echo "❌ Error retrieving records: " . $e->getMessage() . "\n";
        }
    }
    
    private function identifyDuplicates()
    {
        echo "🔍 IDENTIFYING USER_ID DUPLICATES:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            $stmt = $this->pdo->query("
                SELECT user_id, COUNT(*) as count, 
                       GROUP_CONCAT(id ORDER BY created_at) as jobseeker_ids,
                       GROUP_CONCAT(created_at ORDER BY created_at) as created_dates
                FROM jobseekers 
                WHERE user_id IS NOT NULL
                GROUP BY user_id 
                HAVING count > 1
                ORDER BY count DESC
            ");
            $duplicates = $stmt->fetchAll();
            
            if (empty($duplicates)) {
                echo "✅ No user_id duplicates found\n\n";
                return [];
            }
            
            foreach ($duplicates as $dup) {
                echo "🔴 DUPLICATE FOUND:\n";
                echo "   User ID: {$dup['user_id']}\n";
                echo "   Count: {$dup['count']} jobseeker profiles\n";
                echo "   Jobseeker IDs: {$dup['jobseeker_ids']}\n";
                echo "   Created dates: {$dup['created_dates']}\n";
                
                // Show detailed info for each duplicate
                $ids = explode(',', $dup['jobseeker_ids']);
                $dates = explode(',', $dup['created_dates']);
                
                for ($i = 0; $i < count($ids); $i++) {
                    $detailStmt = $this->pdo->prepare("
                        SELECT j.*, u.name as user_name, u.email 
                        FROM jobseekers j 
                        LEFT JOIN users u ON j.user_id = u.id 
                        WHERE j.id = ?
                    ");
                    $detailStmt->execute([trim($ids[$i])]);
                    $detail = $detailStmt->fetch();
                    
                    $fullName = trim(($detail['first_name'] ?? '') . ' ' . ($detail['last_name'] ?? ''));
                    $status = ($i == count($ids) - 1) ? "👑 NEWEST (KEEP)" : "🗑️  OLDER (REMOVE)";
                    
                    echo "   → Profile #{trim($ids[$i])}: Created {$dates[$i]} - $status\n";
                    echo "     Name: $fullName | Email: {$detail['email']}\n";
                    echo "     Profile completion: {$detail['profile_completion_percentage']}%\n";
                    echo "     Total applications: {$detail['total_applications']}\n";
                    echo "     Profile views: {$detail['profile_views']}\n";
                }
                echo "\n";
            }
            
            return $duplicates;
            
        } catch (PDOException $e) {
            echo "❌ Error checking duplicates: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    private function showUserDetails()
    {
        echo "👤 USER ACCOUNT DETAILS:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Show users who have multiple jobseeker profiles
            $stmt = $this->pdo->query("
                SELECT u.id, u.name, u.email, u.role, u.created_at,
                       COUNT(j.id) as profile_count
                FROM users u
                LEFT JOIN jobseekers j ON u.id = j.user_id
                WHERE u.role = 'jobseeker'
                GROUP BY u.id
                HAVING profile_count > 1 OR profile_count = 0
                ORDER BY profile_count DESC
            ");
            $users = $stmt->fetchAll();
            
            foreach ($users as $user) {
                if ($user['profile_count'] > 1) {
                    echo "🔴 User ID {$user['id']}: {$user['name']} ({$user['email']})\n";
                    echo "   → Has {$user['profile_count']} jobseeker profiles (DUPLICATE ISSUE)\n";
                } else {
                    echo "⚠️  User ID {$user['id']}: {$user['name']} ({$user['email']})\n";
                    echo "   → Has NO jobseeker profile (MISSING PROFILE)\n";
                }
            }
            echo "\n";
            
        } catch (PDOException $e) {
            echo "❌ Error checking user details: " . $e->getMessage() . "\n";
        }
    }
    
    private function generateFixScript()
    {
        echo "🔧 GENERATING CLEANUP SCRIPT:\n";
        echo str_repeat("=", 70) . "\n";
        
        // Get duplicates again for script generation
        $stmt = $this->pdo->query("
            SELECT user_id, GROUP_CONCAT(id ORDER BY created_at) as jobseeker_ids
            FROM jobseekers 
            WHERE user_id IS NOT NULL
            GROUP BY user_id 
            HAVING COUNT(*) > 1
        ");
        $duplicates = $stmt->fetchAll();
        
        if (empty($duplicates)) {
            echo "✅ No duplicates found - no cleanup needed!\n";
            return;
        }
        
        $sqlScript = "-- JOBSEEKERS DUPLICATE CLEANUP SCRIPT\n";
        $sqlScript .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $sqlScript .= "-- IMPORTANT: CREATE BACKUP FIRST!\n";
        $sqlScript .= "-- mysqldump -u root -p job_portal jobseekers > jobseekers_backup.sql\n\n";
        
        $phpScript = "<?php\n\n";
        $phpScript .= "// JOBSEEKERS DUPLICATE CLEANUP SCRIPT\n";
        $phpScript .= "// This script removes duplicate jobseeker profiles, keeping the newest one\n\n";
        $phpScript .= "try {\n";
        $phpScript .= "    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [\n";
        $phpScript .= "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION\n";
        $phpScript .= "    ]);\n";
        $phpScript .= "    echo \"✅ Connected to database\\n\\n\";\n\n";
        
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup['jobseeker_ids']);
            // Keep the last (newest) ID, remove the others
            $toRemove = array_slice($ids, 0, -1);
            $toKeep = end($ids);
            
            $sqlScript .= "-- User ID {$dup['user_id']}: Keep profile $toKeep, remove " . implode(', ', $toRemove) . "\n";
            foreach ($toRemove as $removeId) {
                $sqlScript .= "DELETE FROM jobseekers WHERE id = $removeId;\n";
            }
            $sqlScript .= "\n";
            
            $phpScript .= "    // User ID {$dup['user_id']}: Keep profile $toKeep, remove " . implode(', ', $toRemove) . "\n";
            foreach ($toRemove as $removeId) {
                $phpScript .= "    \$pdo->exec(\"DELETE FROM jobseekers WHERE id = $removeId\");\n";
                $phpScript .= "    echo \"🗑️  Removed duplicate jobseeker profile ID: $removeId\\n\";\n";
            }
            $phpScript .= "    echo \"👑 Kept jobseeker profile ID: $toKeep for user {$dup['user_id']}\\n\\n\";\n\n";
        }
        
        $phpScript .= "    echo \"🎉 Cleanup completed successfully!\\n\";\n";
        $phpScript .= "} catch (PDOException \$e) {\n";
        $phpScript .= "    echo \"❌ Error: \" . \$e->getMessage() . \"\\n\";\n";
        $phpScript .= "}\n";
        
        // Write scripts to files
        file_put_contents('cleanup_jobseekers_duplicates.sql', $sqlScript);
        file_put_contents('cleanup_jobseekers_duplicates.php', $phpScript);
        
        echo "📄 Generated cleanup files:\n";
        echo "  • cleanup_jobseekers_duplicates.sql (SQL commands)\n";
        echo "  • cleanup_jobseekers_duplicates.php (PHP script)\n\n";
        
        echo "📋 SUMMARY OF ISSUES FOUND:\n";
        echo "  • " . count($duplicates) . " users have duplicate jobseeker profiles\n";
        
        $totalDuplicates = 0;
        foreach ($duplicates as $dup) {
            $count = count(explode(',', $dup['jobseeker_ids']));
            $totalDuplicates += ($count - 1); // -1 because we keep one
        }
        echo "  • $totalDuplicates duplicate profiles will be removed\n";
        echo "  • Newest profile for each user will be kept\n\n";
        
        echo "⚠️  BEFORE RUNNING CLEANUP:\n";
        echo "1. Create backup: mysqldump -u root -p job_portal jobseekers > backup.sql\n";
        echo "2. Review the generated files\n";
        echo "3. Run: php cleanup_jobseekers_duplicates.php\n\n";
    }
}

// Run the analysis and fix generator
$fixer = new JobseekersDuplicateFixer();
$fixer->analyzeAndFix();

echo str_repeat("=", 70) . "\n";
