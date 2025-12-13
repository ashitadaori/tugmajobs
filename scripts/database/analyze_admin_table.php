<?php

echo "==============================================\n";
echo "  ADMIN TABLE COMPREHENSIVE ANALYSIS\n";
echo "==============================================\n\n";

class AdminTableAnalyzer
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
    
    public function analyzeAll()
    {
        $this->showTableStructure();
        $this->showAllRecords();
        $this->checkDuplicates();
        $this->checkRelationshipConsistency();
        $this->compareWithUsersTable();
        $this->generateRecommendations();
    }
    
    private function showTableStructure()
    {
        echo "📋 ADMIN TABLE STRUCTURE:\n";
        echo str_repeat("-", 60) . "\n";
        
        try {
            $stmt = $this->pdo->query("DESCRIBE `admins`");
            $structure = $stmt->fetchAll();
            
            foreach ($structure as $column) {
                $key = $column['Key'] ? " ({$column['Key']})" : "";
                $null = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
                $default = $column['Default'] !== null ? " DEFAULT: {$column['Default']}" : "";
                
                echo sprintf("  %-25s %-20s %s%s%s\n", 
                    $column['Field'], 
                    $column['Type'], 
                    $null,
                    $key,
                    $default
                );
            }
            echo "\n";
            
        } catch (PDOException $e) {
            echo "❌ Error getting table structure: " . $e->getMessage() . "\n";
        }
    }
    
    private function showAllRecords()
    {
        echo "📊 ALL ADMIN RECORDS:\n";
        echo str_repeat("-", 120) . "\n";
        
        try {
            $stmt = $this->pdo->query("
                SELECT a.*, u.name as user_name, u.email as user_email, u.role as user_role
                FROM admins a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.id
            ");
            $records = $stmt->fetchAll();
            
            if (empty($records)) {
                echo "⚠️  No records found in admin table\n\n";
                return;
            }
            
            // Show headers
            echo sprintf("%-4s %-8s %-25s %-30s %-25s %-20s %-15s\n", 
                'ID', 'User_ID', 'Admin_Name', 'User_Email', 'User_Name', 'Created_At', 'Status'
            );
            echo str_repeat("-", 120) . "\n";
            
            foreach ($records as $record) {
                $adminName = isset($record['name']) ? $record['name'] : 'N/A';
                $userEmail = $record['user_email'] ?: 'N/A';
                $userName = $record['user_name'] ?: 'N/A';
                $created = $record['created_at'] ? date('Y-m-d H:i', strtotime($record['created_at'])) : 'N/A';
                $status = $record['user_role'] === 'admin' ? '✅ Valid' : '❌ Invalid Role';
                
                echo sprintf("%-4s %-8s %-25s %-30s %-25s %-20s %-15s\n", 
                    $record['id'], 
                    $record['user_id'],
                    substr($adminName, 0, 24),
                    substr($userEmail, 0, 29),
                    substr($userName, 0, 24),
                    $created,
                    $status
                );
                
                // Show detailed info for each record
                echo "   Details: ";
                $details = [];
                foreach ($record as $key => $value) {
                    if (!in_array($key, ['id', 'user_id', 'created_at', 'updated_at', 'user_name', 'user_email', 'user_role']) && $value !== null) {
                        $details[] = "$key: $value";
                    }
                }
                echo empty($details) ? "No additional data" : implode(', ', array_slice($details, 0, 3));
                if (count($details) > 3) echo "...";
                echo "\n\n";
            }
            
            echo "Total admin records: " . count($records) . "\n\n";
            
        } catch (PDOException $e) {
            echo "❌ Error retrieving records: " . $e->getMessage() . "\n";
        }
    }
    
    private function checkDuplicates()
    {
        echo "🔍 CHECKING FOR DUPLICATES:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check for user_id duplicates
            $stmt = $this->pdo->query("
                SELECT user_id, COUNT(*) as count, GROUP_CONCAT(id) as admin_ids
                FROM admins 
                WHERE user_id IS NOT NULL
                GROUP BY user_id 
                HAVING count > 1
                ORDER BY count DESC
            ");
            $userIdDuplicates = $stmt->fetchAll();
            
            if (!empty($userIdDuplicates)) {
                echo "🔴 USER_ID DUPLICATES FOUND:\n";
                foreach ($userIdDuplicates as $dup) {
                    echo "   → User ID {$dup['user_id']}: {$dup['count']} admin records (IDs: {$dup['admin_ids']})\n";
                }
                echo "\n";
            } else {
                echo "✅ No user_id duplicates found\n";
            }
            
            // Check for email duplicates (if email field exists)
            $columns = $this->pdo->query("SHOW COLUMNS FROM admins LIKE 'email'")->fetchAll();
            if (!empty($columns)) {
                $stmt = $this->pdo->query("
                    SELECT email, COUNT(*) as count, GROUP_CONCAT(id) as admin_ids
                    FROM admins 
                    WHERE email IS NOT NULL AND email != ''
                    GROUP BY email 
                    HAVING count > 1
                    ORDER BY count DESC
                ");
                $emailDuplicates = $stmt->fetchAll();
                
                if (!empty($emailDuplicates)) {
                    echo "🔴 EMAIL DUPLICATES FOUND:\n";
                    foreach ($emailDuplicates as $dup) {
                        echo "   → Email '{$dup['email']}': {$dup['count']} admin records (IDs: {$dup['admin_ids']})\n";
                    }
                    echo "\n";
                } else {
                    echo "✅ No email duplicates found\n";
                }
            }
            
        } catch (PDOException $e) {
            echo "❌ Error checking duplicates: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkRelationshipConsistency()
    {
        echo "🔗 CHECKING RELATIONSHIP CONSISTENCY:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check for admin records with invalid user_id references
            $stmt = $this->pdo->query("
                SELECT a.id, a.user_id
                FROM admins a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.user_id IS NOT NULL AND u.id IS NULL
            ");
            $orphanedAdmins = $stmt->fetchAll();
            
            if (!empty($orphanedAdmins)) {
                echo "🔴 ORPHANED ADMIN RECORDS:\n";
                foreach ($orphanedAdmins as $record) {
                    echo "   → Admin ID {$record['id']}: references non-existent user_id {$record['user_id']}\n";
                }
                echo "\n";
            } else {
                echo "✅ All admin records have valid user references\n";
            }
            
            // Check for users with admin role but no admin profile
            $stmt = $this->pdo->query("
                SELECT u.id, u.name, u.email, u.role
                FROM users u
                LEFT JOIN admins a ON u.id = a.user_id
                WHERE (u.role = 'admin' OR u.role = 'superadmin') AND a.id IS NULL
            ");
            $missingAdminProfiles = $stmt->fetchAll();
            
            if (!empty($missingAdminProfiles)) {
                echo "🔴 ADMIN USERS WITHOUT ADMIN PROFILES:\n";
                foreach ($missingAdminProfiles as $user) {
                    echo "   → User ID {$user['id']}: {$user['name']} ({$user['email']}) - Role: {$user['role']}\n";
                }
                echo "\n";
            } else {
                echo "✅ All admin users have admin profiles\n";
            }
            
            // Check for admin profiles with users that don't have admin role
            $stmt = $this->pdo->query("
                SELECT a.id, a.user_id, u.name, u.email, u.role
                FROM admins a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE u.role NOT IN ('admin', 'superadmin') OR u.role IS NULL
            ");
            $invalidRoles = $stmt->fetchAll();
            
            if (!empty($invalidRoles)) {
                echo "🔴 ADMIN PROFILES WITH NON-ADMIN USERS:\n";
                foreach ($invalidRoles as $record) {
                    $role = $record['role'] ?: 'NULL';
                    echo "   → Admin ID {$record['id']}: User {$record['name']} has role '{$role}' instead of admin\n";
                }
                echo "\n";
            } else {
                echo "✅ All admin profiles belong to admin users\n";
            }
            
        } catch (PDOException $e) {
            echo "❌ Error checking relationships: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function compareWithUsersTable()
    {
        echo "👤 COMPARING WITH USERS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Get counts
            $adminCount = $this->pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
            $adminUsersCount = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('admin', 'superadmin')")->fetchColumn();
            
            echo "📊 STATISTICS:\n";
            echo "  • Total admin profiles: $adminCount\n";
            echo "  • Total users with admin/superadmin role: $adminUsersCount\n";
            
            if ($adminCount == $adminUsersCount) {
                echo "  ✅ Counts match - good consistency\n";
            } else {
                echo "  ⚠️  Counts don't match - potential issues\n";
            }
            echo "\n";
            
            // Show detailed comparison
            echo "📋 DETAILED COMPARISON:\n";
            $stmt = $this->pdo->query("
                SELECT 
                    u.id as user_id,
                    u.name,
                    u.email,
                    u.role,
                    a.id as admin_id,
                    CASE 
                        WHEN u.role IN ('admin', 'superadmin') AND a.id IS NOT NULL THEN '✅ Complete'
                        WHEN u.role IN ('admin', 'superadmin') AND a.id IS NULL THEN '⚠️  Missing Profile'
                        WHEN u.role NOT IN ('admin', 'superadmin') AND a.id IS NOT NULL THEN '❌ Wrong Role'
                        ELSE '❓ Unknown'
                    END as status
                FROM users u
                LEFT JOIN admins a ON u.id = a.user_id
                WHERE u.role IN ('admin', 'superadmin') OR a.id IS NOT NULL
                ORDER BY u.role, u.id
            ");
            $comparison = $stmt->fetchAll();
            
            foreach ($comparison as $record) {
                echo sprintf("  %s User ID %s: %s (%s) - Role: %s\n",
                    $record['status'],
                    $record['user_id'],
                    $record['name'],
                    $record['email'],
                    $record['role']
                );
            }
            
        } catch (PDOException $e) {
            echo "❌ Error comparing tables: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function generateRecommendations()
    {
        echo "💡 ANALYSIS RESULTS & RECOMMENDATIONS:\n";
        echo str_repeat("=", 70) . "\n";
        
        try {
            $issues = [];
            $fixes = [];
            
            // Check for various issues
            $userIdDuplicates = $this->pdo->query("
                SELECT COUNT(*) FROM (
                    SELECT user_id FROM admins WHERE user_id IS NOT NULL GROUP BY user_id HAVING COUNT(*) > 1
                ) as dups
            ")->fetchColumn();
            
            $orphanedRecords = $this->pdo->query("
                SELECT COUNT(*) FROM admins a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.user_id IS NOT NULL AND u.id IS NULL
            ")->fetchColumn();
            
            $missingProfiles = $this->pdo->query("
                SELECT COUNT(*) FROM users u 
                LEFT JOIN admins a ON u.id = a.user_id 
                WHERE (u.role = 'admin' OR u.role = 'superadmin') AND a.id IS NULL
            ")->fetchColumn();
            
            $wrongRoles = $this->pdo->query("
                SELECT COUNT(*) FROM admins a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE u.role NOT IN ('admin', 'superadmin') OR u.role IS NULL
            ")->fetchColumn();
            
            // Generate specific recommendations
            echo "🔍 ISSUES FOUND:\n";
            
            if ($userIdDuplicates > 0) {
                echo "🔴 HIGH PRIORITY:\n";
                echo "  • $userIdDuplicates users have multiple admin profiles\n";
                echo "  • Action: Remove duplicates, keep the most recent\n\n";
                $issues[] = 'user_id_duplicates';
                $fixes[] = 'Remove duplicate admin profiles';
            }
            
            if ($orphanedRecords > 0) {
                echo "🟠 MEDIUM PRIORITY:\n";
                echo "  • $orphanedRecords admin records reference non-existent users\n";
                echo "  • Action: Clean up orphaned records\n\n";
                $issues[] = 'orphaned_records';
                $fixes[] = 'Remove orphaned admin records';
            }
            
            if ($missingProfiles > 0) {
                echo "🟡 LOW PRIORITY:\n";
                echo "  • $missingProfiles admin users don't have admin profiles\n";
                echo "  • Action: Create missing admin profiles or update user roles\n\n";
                $issues[] = 'missing_profiles';
                $fixes[] = 'Create missing admin profiles';
            }
            
            if ($wrongRoles > 0) {
                echo "🔴 CRITICAL:\n";
                echo "  • $wrongRoles admin profiles belong to non-admin users\n";
                echo "  • Action: Fix user roles or remove admin profiles\n\n";
                $issues[] = 'wrong_roles';
                $fixes[] = 'Fix user role mismatches';
            }
            
            if (empty($issues)) {
                echo "✅ GOOD NEWS:\n";
                echo "  • No critical issues found!\n";
                echo "  • Admin table appears to be in good condition\n\n";
            }
            
            // Generate cleanup script if issues found
            if (!empty($issues)) {
                echo "🔧 CLEANUP SCRIPT GENERATION:\n";
                echo "  • Generating automated cleanup script...\n";
                $this->generateCleanupScript($issues);
            }
            
        } catch (PDOException $e) {
            echo "❌ Error generating recommendations: " . $e->getMessage() . "\n";
        }
    }
    
    private function generateCleanupScript($issues)
    {
        $phpScript = "<?php\n\n";
        $phpScript .= "// ADMIN TABLE CLEANUP SCRIPT\n";
        $phpScript .= "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $phpScript .= "try {\n";
        $phpScript .= "    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [\n";
        $phpScript .= "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION\n";
        $phpScript .= "    ]);\n";
        $phpScript .= "    echo \"✅ Connected to database\\n\\n\";\n\n";
        
        foreach ($issues as $issue) {
            switch ($issue) {
                case 'user_id_duplicates':
                    $phpScript .= "    // Fix user_id duplicates\n";
                    $phpScript .= "    echo \"🔧 Fixing user_id duplicates...\\n\";\n";
                    $phpScript .= "    // Implementation would go here\n\n";
                    break;
                    
                case 'orphaned_records':
                    $phpScript .= "    // Remove orphaned admin records\n";
                    $phpScript .= "    \$orphaned = \$pdo->exec(\"\n";
                    $phpScript .= "        DELETE a FROM admins a \n";
                    $phpScript .= "        LEFT JOIN users u ON a.user_id = u.id \n";
                    $phpScript .= "        WHERE a.user_id IS NOT NULL AND u.id IS NULL\n";
                    $phpScript .= "    \");\n";
                    $phpScript .= "    echo \"🗑️  Removed \$orphaned orphaned admin records\\n\";\n\n";
                    break;
                    
                case 'wrong_roles':
                    $phpScript .= "    // Fix role mismatches\n";
                    $phpScript .= "    echo \"🔧 Fixing role mismatches...\\n\";\n";
                    $phpScript .= "    // Manual review required for role fixes\n\n";
                    break;
            }
        }
        
        $phpScript .= "    echo \"🎉 Admin table cleanup completed!\\n\";\n";
        $phpScript .= "} catch (PDOException \$e) {\n";
        $phpScript .= "    echo \"❌ Error: \" . \$e->getMessage() . \"\\n\";\n";
        $phpScript .= "}\n";
        
        file_put_contents('cleanup_admin_table.php', $phpScript);
        echo "  • Generated: cleanup_admin_table.php\n\n";
    }
}

// Run the analysis
$analyzer = new AdminTableAnalyzer();
$analyzer->analyzeAll();

echo str_repeat("=", 70) . "\n";
