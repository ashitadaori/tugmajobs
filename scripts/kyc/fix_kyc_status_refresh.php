<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;
use Illuminate\Support\Facades\DB;

echo "🔧 KYC Status Refresh & Fix\n";
echo "============================\n\n";

try {
    // Step 1: Check current status
    echo "📊 Step 1: Checking current KYC statuses...\n";
    $users = User::all();
    
    foreach ($users as $user) {
        echo "User #{$user->id} ({$user->name}): {$user->kyc_status}\n";
        
        // Check if user has completed KYC but status is still pending/in_progress
        $latestKycVerification = $user->kycVerifications()->latest()->first();
        
        if ($latestKycVerification && $latestKycVerification->status === 'verified' && $user->kyc_status !== 'verified') {
            echo "   ⚠️  Status mismatch detected! KYC record shows 'verified' but user status is '{$user->kyc_status}'\n";
            
            // Update user status to match KYC verification
            $user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => $latestKycVerification->verified_at ?? now(),
                'kyc_completed_at' => $latestKycVerification->completed_at ?? now()
            ]);
            
            echo "   ✅ Fixed: Updated user status to 'verified'\n";
        }
    }
    
    echo "\n";
    
    // Step 2: Clear any stuck sessions
    echo "📝 Step 2: Clearing stuck 'in_progress' sessions...\n";
    $stuckUsers = User::where('kyc_status', 'in_progress')
        ->where('updated_at', '<', now()->subMinutes(30))
        ->get();
        
    if ($stuckUsers->count() > 0) {
        foreach ($stuckUsers as $user) {
            // Check if they have a completed verification
            $completedVerification = $user->kycVerifications()
                ->where('status', 'verified')
                ->latest()
                ->first();
                
            if ($completedVerification) {
                $user->update([
                    'kyc_status' => 'verified',
                    'kyc_verified_at' => $completedVerification->verified_at ?? now(),
                    'kyc_completed_at' => $completedVerification->completed_at ?? now(),
                    'kyc_session_id' => null
                ]);
                echo "   ✅ User #{$user->id}: Set to verified (had completed verification)\n";
            } else {
                $user->update([
                    'kyc_status' => 'pending',
                    'kyc_session_id' => null
                ]);
                echo "   🔄 User #{$user->id}: Reset to pending (no completed verification found)\n";
            }
        }
    } else {
        echo "   ℹ️  No stuck sessions found\n";
    }
    
    echo "\n";
    
    // Step 3: Create JavaScript function to refresh KYC status on frontend
    echo "⚙️  Step 3: Creating frontend KYC status refresher...\n";
    
    $jsContent = '/**
 * KYC Status Refresher - Fixes frontend caching issues
 * Add this to your main layout or dashboard
 */

// Function to refresh KYC status from server
function refreshKycStatus() {
    console.log("[KYC] Refreshing status from server...");
    
    fetch("/api/user/kyc-status", {
        method: "GET",
        headers: {
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\')?.getAttribute("content") || ""
        },
        credentials: "same-origin"
    })
    .then(response => response.json())
    .then(data => {
        console.log("[KYC] Status received:", data);
        
        // Update any KYC status displays on the page
        updateKycStatusDisplay(data.kyc_status);
        
        // Hide verification modal if user is now verified
        if (data.kyc_status === "verified") {
            hideKycModals();
        }
    })
    .catch(error => {
        console.error("[KYC] Error refreshing status:", error);
    });
}

// Function to update KYC status display elements
function updateKycStatusDisplay(status) {
    // Update status badges
    const statusElements = document.querySelectorAll(".kyc-status, [data-kyc-status]");
    statusElements.forEach(el => {
        el.setAttribute("data-kyc-status", status);
        
        // Update text content
        const statusText = {
            "pending": "Not Verified",
            "in_progress": "In Progress",
            "verified": "Verified",
            "failed": "Failed",
            "expired": "Expired"
        };
        
        if (el.textContent.includes("KYC") || el.textContent.includes("Verification")) {
            el.textContent = statusText[status] || "Not Verified";
        }
    });
    
    // Update verification buttons
    const verificationButtons = document.querySelectorAll("[onclick*=\'verification\'], .start-kyc-btn");
    verificationButtons.forEach(btn => {
        if (status === "verified") {
            btn.style.display = "none";
        } else {
            btn.style.display = "";
        }
    });
}

// Function to hide KYC modals
function hideKycModals() {
    // Hide any open KYC modals
    const kycModals = [
        "#kycModal",
        "#kycVerificationModal", 
        "#verificationAlertModal"
    ];
    
    kycModals.forEach(modalId => {
        const modal = document.querySelector(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    });
    
    console.log("[KYC] Hidden verification modals");
}

// Auto-refresh status every 10 seconds if verification is in progress
function startKycStatusPolling() {
    const interval = setInterval(() => {
        const currentStatus = document.querySelector("[data-kyc-status]")?.getAttribute("data-kyc-status");
        
        if (currentStatus === "in_progress" || currentStatus === "pending") {
            refreshKycStatus();
        } else if (currentStatus === "verified") {
            clearInterval(interval);
            console.log("[KYC] Polling stopped - user is verified");
        }
    }, 10000); // Check every 10 seconds
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function() {
    console.log("[KYC] Status refresher initialized");
    
    // Refresh status immediately
    refreshKycStatus();
    
    // Start polling if needed
    startKycStatusPolling();
});

// Make functions available globally
window.refreshKycStatus = refreshKycStatus;
window.updateKycStatusDisplay = updateKycStatusDisplay;
window.hideKycModals = hideKycModals;
';
    
    file_put_contents('public/assets/js/kyc-status-refresher.js', $jsContent);
    echo "   ✅ Created: public/assets/js/kyc-status-refresher.js\n";
    
    echo "\n";
    
    // Step 4: Create API route for status checking
    echo "🔗 Step 4: API route for KYC status checking...\n";
    echo "   ℹ️  Add this route to your routes/api.php file:\n";
    echo "\n";
    echo "   Route::get('/user/kyc-status', function (Request \$request) {\n";
    echo "       if (!Auth::check()) {\n";
    echo "           return response()->json(['error' => 'Unauthorized'], 401);\n";
    echo "       }\n";
    echo "\n";
    echo "       \$user = Auth::user();\n";
    echo "       return response()->json([\n";
    echo "           'kyc_status' => \$user->kyc_status,\n";
    echo "           'is_verified' => \$user->isKycVerified(),\n";
    echo "           'status_text' => \$user->kyc_status_text,\n";
    echo "           'updated_at' => \$user->updated_at->toISOString()\n";
    echo "       ]);\n";
    echo "   });\n";
    echo "\n";
    
    // Step 5: Final status report
    echo "📈 Step 5: Final status report...\n";
    $finalStats = [
        'total_users' => User::count(),
        'verified_users' => User::where('kyc_status', 'verified')->count(),
        'pending_users' => User::where('kyc_status', 'pending')->count(),
        'in_progress_users' => User::where('kyc_status', 'in_progress')->count(),
        'failed_users' => User::where('kyc_status', 'failed')->count()
    ];
    
    foreach ($finalStats as $key => $value) {
        echo "   - " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
    }
    
    echo "\n";
    echo "🎯 Instructions to Complete the Fix:\n";
    echo "=====================================\n";
    echo "1. Add the API route shown above to routes/api.php\n";
    echo "2. Add this line to your main layout file (before closing </body>):\n";
    echo "   <script src='{{ asset(\"assets/js/kyc-status-refresher.js\") }}'></script>\n";
    echo "3. Clear browser cache and refresh the page\n";
    echo "4. The verification popup should no longer appear for verified users\n";
    echo "\n";
    echo "🔧 Manual Testing:\n";
    echo "- Open browser console and check for '[KYC]' logs\n";
    echo "- Verified users should not see the verification modal\n";
    echo "- Status should update automatically without page refresh\n";
    
} catch (Exception $e) {
    echo "❌ Error during KYC fix: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ KYC Status Fix Complete!\n";
echo "===========================\n";
echo "🎉 The verification popup issue should now be resolved.\n";
echo "🔄 Users' KYC statuses have been synchronized with their verification records.\n";
echo "📱 Frontend will now automatically refresh status to prevent caching issues.\n\n";
