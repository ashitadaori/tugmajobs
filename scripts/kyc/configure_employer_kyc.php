<?php

/**
 * Configure Employer KYC Verification Mode
 *
 * This script helps you configure how employer verification works.
 */

echo "═══════════════════════════════════════════════════════════\n";
echo "          EMPLOYER KYC CONFIGURATION TOOL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$envFile = __DIR__ . '/../../.env';

if (!file_exists($envFile)) {
    echo "❌ Error: .env file not found at: {$envFile}\n";
    echo "   Please make sure you're running this from the project root.\n";
    exit(1);
}

// Read current .env content
$envContent = file_get_contents($envFile);

// Check current settings
$currentKycOnly = preg_match('/EMPLOYER_KYC_ONLY=true/i', $envContent);
$currentDisabled = preg_match('/DISABLE_KYC_FOR_EMPLOYERS=true/i', $envContent);

echo "📋 CURRENT CONFIGURATION:\n";
if ($currentDisabled) {
    echo "   Mode: All verification DISABLED (testing mode)\n";
    echo "   ⚠️  WARNING: This should only be used for development!\n";
} elseif ($currentKycOnly) {
    echo "   Mode: KYC-Only (like jobseekers)\n";
    echo "   ✅ Employers need only KYC verification to post jobs\n";
} else {
    echo "   Mode: Full Verification (default)\n";
    echo "   📄 Employers need both KYC + document approval\n";
}
echo "\n";

echo "───────────────────────────────────────────────────────────\n";
echo "AVAILABLE MODES:\n";
echo "───────────────────────────────────────────────────────────\n\n";

echo "1. KYC-Only Mode (Recommended for Testing) ⭐\n";
echo "   - Employers only need KYC verification\n";
echo "   - Works exactly like jobseeker verification\n";
echo "   - Faster onboarding, easier testing\n";
echo "   - No admin document approval needed\n\n";

echo "2. Full Verification Mode (Production Default)\n";
echo "   - Requires KYC verification\n";
echo "   - PLUS document approval by admin\n";
echo "   - More secure and compliant\n";
echo "   - Slower onboarding process\n\n";

echo "3. Disabled Mode (Development Only) ⚠️\n";
echo "   - NO verification required at all\n";
echo "   - USE ONLY FOR LOCAL DEVELOPMENT\n";
echo "   - NEVER use in production!\n\n";

echo "───────────────────────────────────────────────────────────\n";
echo "SELECT MODE:\n";
echo "───────────────────────────────────────────────────────────\n\n";

echo "Enter your choice (1, 2, or 3): ";
$choice = trim(fgets(STDIN));

switch ($choice) {
    case '1':
        // KYC-Only Mode
        echo "\n✅ Configuring KYC-Only Mode...\n";
        $envContent = updateEnvVariable($envContent, 'EMPLOYER_KYC_ONLY', 'true');
        $envContent = updateEnvVariable($envContent, 'DISABLE_KYC_FOR_EMPLOYERS', 'false');
        $mode = "KYC-Only";
        $description = "Employers need only KYC verification (like jobseekers)";
        break;

    case '2':
        // Full Verification Mode
        echo "\n✅ Configuring Full Verification Mode...\n";
        $envContent = updateEnvVariable($envContent, 'EMPLOYER_KYC_ONLY', 'false');
        $envContent = updateEnvVariable($envContent, 'DISABLE_KYC_FOR_EMPLOYERS', 'false');
        $mode = "Full Verification";
        $description = "Employers need KYC + document approval";
        break;

    case '3':
        // Disabled Mode
        echo "\n⚠️  Configuring Disabled Mode (Testing Only)...\n";
        echo "   WARNING: This disables all verification checks!\n";
        echo "   Confirm? (yes/no): ";
        $confirm = trim(fgets(STDIN));
        if (strtolower($confirm) !== 'yes') {
            echo "\n❌ Configuration cancelled.\n";
            exit(0);
        }
        $envContent = updateEnvVariable($envContent, 'EMPLOYER_KYC_ONLY', 'false');
        $envContent = updateEnvVariable($envContent, 'DISABLE_KYC_FOR_EMPLOYERS', 'true');
        $mode = "Disabled (Testing)";
        $description = "NO verification required";
        break;

    default:
        echo "\n❌ Invalid choice. Configuration cancelled.\n";
        exit(1);
}

// Write updated .env file
if (file_put_contents($envFile, $envContent)) {
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "✅ CONFIGURATION UPDATED SUCCESSFULLY!\n";
    echo "═══════════════════════════════════════════════════════════\n\n";

    echo "📌 NEW CONFIGURATION:\n";
    echo "   Mode: {$mode}\n";
    echo "   Description: {$description}\n\n";

    echo "🔄 NEXT STEPS:\n";
    echo "   1. Clear config cache:\n";
    echo "      php artisan config:clear\n\n";

    if ($choice === '1' || $choice === '2') {
        echo "   2. Reset employer KYC status (if testing):\n";
        echo "      php scripts/kyc/reset_kyc.php list\n";
        echo "      php scripts/kyc/reset_kyc.php [USER_ID]\n\n";

        echo "   3. Test employer login and job posting:\n";
        echo "      - Login as employer\n";
        echo "      - Complete KYC verification\n";
        if ($choice === '1') {
            echo "      - Try posting a job (should work immediately after KYC)\n";
        } else {
            echo "      - Submit required documents\n";
            echo "      - Wait for admin approval\n";
            echo "      - Then try posting a job\n";
        }
    } else {
        echo "   2. Test employer login:\n";
        echo "      - Login as employer\n";
        echo "      - Try posting a job (should work immediately)\n";
    }

    echo "\n📚 DOCUMENTATION:\n";
    echo "   See docs/EMPLOYER_KYC_GUIDE.md for complete details\n\n";

} else {
    echo "\n❌ Error: Failed to write .env file\n";
    echo "   Please check file permissions.\n";
    exit(1);
}

/**
 * Update or add environment variable
 */
function updateEnvVariable($content, $key, $value) {
    // Check if variable exists
    if (preg_match("/^{$key}=.*/m", $content)) {
        // Update existing
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
    } else {
        // Add new variable
        $content .= "\n{$key}={$value}\n";
    }
    return $content;
}

echo "═══════════════════════════════════════════════════════════\n";
echo "Configuration complete! Don't forget to clear cache.\n";
echo "═══════════════════════════════════════════════════════════\n";
