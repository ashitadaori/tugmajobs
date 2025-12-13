<?php

echo "==============================================\n";
echo "  DATABASE CLEANUP LAUNCHER\n";
echo "==============================================\n\n";

echo "🚀 Attempting to start MySQL service...\n";

// Try different ways to start MySQL service
$mysqlStarted = false;

// Method 1: Try to start as Windows service
$services = ['mysql', 'MySQL', 'MySQL80', 'MySQL57', 'MySQL56'];
foreach ($services as $service) {
    echo "Trying to start service: $service\n";
    $result = shell_exec("net start $service 2>&1");
    if (strpos($result, 'successfully') !== false || strpos($result, 'already') !== false) {
        echo "✅ MySQL service started or already running\n";
        $mysqlStarted = true;
        break;
    }
}

// Method 2: Try XAMPP MySQL directly
if (!$mysqlStarted) {
    $xamppPaths = [
        'C:\\xampp\\mysql\\bin\\mysqld.exe',
        'D:\\xampp\\mysql\\bin\\mysqld.exe',
        'C:\\xampp\\mysql\\bin\\mysqld_safe'
    ];
    
    foreach ($xamppPaths as $path) {
        if (file_exists($path)) {
            echo "Found MySQL at: $path\n";
            echo "⚠️  Please start MySQL manually from XAMPP Control Panel\n";
            echo "   Or run: $path --console\n";
            break;
        }
    }
}

echo "\n🔍 Testing database connection...\n";

// Test if we can connect to database
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Test connection
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connection successful!\n";
    echo "Database: " . DB::getDatabaseName() . "\n\n";
    
    // Run the analysis
    echo "🚀 Starting database cleanup analysis...\n\n";
    include 'database_cleanup_analysis.php';
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
    
    echo "📋 TROUBLESHOOTING STEPS:\n";
    echo "1. Make sure XAMPP is installed\n";
    echo "2. Start MySQL from XAMPP Control Panel\n";
    echo "3. Verify database 'job_portal' exists\n";
    echo "4. Check your .env file database settings\n\n";
    
    echo "🔧 Manual steps to start MySQL:\n";
    echo "• Open XAMPP Control Panel\n";
    echo "• Click 'Start' next to MySQL\n";
    echo "• Wait for it to show as 'Running'\n";
    echo "• Then run this script again\n\n";
    
    echo "Or try these commands:\n";
    if (file_exists('C:\\xampp\\mysql\\bin\\mysqld.exe')) {
        echo "• C:\\xampp\\mysql\\bin\\mysqld.exe --console\n";
    }
    if (file_exists('C:\\xampp\\xampp-control.exe')) {
        echo "• C:\\xampp\\xampp-control.exe\n";
    }
    
    exit(1);
}
