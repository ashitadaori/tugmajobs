<?php

echo "🔍 ADMIN PERMISSIONS & SETTINGS ANALYSIS\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=job_portal', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Get admin details with user info
    $stmt = $pdo->query("
        SELECT a.*, u.name, u.email, u.role, u.created_at as user_created_at
        FROM admins a 
        JOIN users u ON a.user_id = u.id
    ");
    $admin = $stmt->fetch();
    
    if (!$admin) {
        echo "❌ No admin records found!\n";
        exit(1);
    }
    
    echo "👤 ADMIN USER DETAILS:\n";
    echo "  • ID: {$admin['id']}\n";
    echo "  • Name: {$admin['name']}\n";
    echo "  • Email: {$admin['email']}\n";
    echo "  • User Role: {$admin['role']}\n";
    echo "  • Admin Level: {$admin['admin_level']}\n";
    echo "  • Department: " . ($admin['department'] ?: 'Not Set') . "\n";
    echo "  • Position: " . ($admin['position'] ?: 'Not Set') . "\n";
    echo "  • Status: {$admin['status']}\n";
    echo "  • User Created: " . date('Y-m-d H:i:s', strtotime($admin['user_created_at'])) . "\n";
    echo "  • Admin Profile Created: " . date('Y-m-d H:i:s', strtotime($admin['created_at'])) . "\n\n";
    
    echo "🔐 ADMIN PERMISSIONS:\n";
    $permissions = [
        'can_manage_users' => 'Manage Users',
        'can_manage_jobs' => 'Manage Jobs', 
        'can_manage_employers' => 'Manage Employers',
        'can_view_analytics' => 'View Analytics',
        'can_manage_settings' => 'Manage Settings',
        'can_manage_admins' => 'Manage Admins'
    ];
    
    $enabledPermissions = 0;
    foreach ($permissions as $key => $label) {
        $enabled = $admin[$key] ? '✅' : '❌';
        echo "  • $label: $enabled\n";
        if ($admin[$key]) $enabledPermissions++;
    }
    echo "\n";
    
    echo "🕒 ACTIVITY & SECURITY:\n";
    echo "  • Last Login: " . ($admin['last_login_at'] ? date('Y-m-d H:i:s', strtotime($admin['last_login_at'])) : 'Never') . "\n";
    echo "  • Last IP: " . ($admin['last_login_ip'] ?: 'Not recorded') . "\n";
    echo "  • Actions Performed: {$admin['actions_performed']}\n";
    echo "  • Force Password Change: " . ($admin['force_password_change'] ? 'Yes' : 'No') . "\n";
    echo "  • Password Last Changed: " . ($admin['password_changed_at'] ? date('Y-m-d H:i:s', strtotime($admin['password_changed_at'])) : 'Not recorded') . "\n";
    echo "  • Promoted At: " . ($admin['promoted_at'] ? date('Y-m-d H:i:s', strtotime($admin['promoted_at'])) : 'Not recorded') . "\n";
    echo "  • Promoted By: " . ($admin['promoted_by'] ?: 'Not recorded') . "\n\n";
    
    echo "📊 ANALYSIS RESULTS:\n";
    echo str_repeat('-', 40) . "\n";
    
    $issues = [];
    $recommendations = [];
    
    // Check permissions
    if ($enabledPermissions == 0) {
        $issues[] = "🔴 CRITICAL: Admin has NO permissions enabled!";
        $recommendations[] = "Enable appropriate admin permissions immediately";
    } elseif ($enabledPermissions < 3) {
        $issues[] = "🟡 WARNING: Admin has limited permissions ({$enabledPermissions}/6)";
        $recommendations[] = "Review and enable necessary admin permissions";
    } else {
        echo "✅ Admin has {$enabledPermissions}/6 permissions enabled - Good\n";
    }
    
    // Check security settings
    if (!$admin['last_login_at']) {
        $issues[] = "⚠️  Admin has never logged in";
        $recommendations[] = "Verify admin account is working properly";
    }
    
    if ($admin['status'] !== 'active') {
        $issues[] = "🔴 Admin status is: {$admin['status']}";
        $recommendations[] = "Activate admin account if needed";
    }
    
    if (!$admin['department']) {
        $issues[] = "ℹ️  Admin department not set";
        $recommendations[] = "Set admin department for better organization";
    }
    
    if (!$admin['position']) {
        $issues[] = "ℹ️  Admin position not set"; 
        $recommendations[] = "Set admin position for clarity";
    }
    
    // Display issues and recommendations
    if (!empty($issues)) {
        echo "\n🚨 ISSUES FOUND:\n";
        foreach ($issues as $issue) {
            echo "  $issue\n";
        }
    }
    
    if (!empty($recommendations)) {
        echo "\n💡 RECOMMENDATIONS:\n";
        foreach ($recommendations as $rec) {
            echo "  • $rec\n";
        }
    }
    
    if (empty($issues)) {
        echo "✅ Admin table is in excellent condition!\n";
        echo "   No issues found with admin configuration.\n";
    }
    
    // Check for accessible modules and permissions JSON
    if ($admin['accessible_modules']) {
        echo "\n📋 ACCESSIBLE MODULES:\n";
        $modules = json_decode($admin['accessible_modules'], true);
        if ($modules) {
            foreach ($modules as $module) {
                echo "  • $module\n";
            }
        } else {
            echo "  • Raw data: {$admin['accessible_modules']}\n";
        }
    }
    
    if ($admin['permissions']) {
        echo "\n🔧 DETAILED PERMISSIONS:\n";
        $perms = json_decode($admin['permissions'], true);
        if ($perms) {
            foreach ($perms as $key => $value) {
                echo "  • $key: $value\n";
            }
        } else {
            echo "  • Raw data: " . substr($admin['permissions'], 0, 100) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 50) . "\n";
