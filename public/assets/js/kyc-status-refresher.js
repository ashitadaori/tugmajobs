/**
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
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || ""
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
    const verificationButtons = document.querySelectorAll("[onclick*='verification'], .start-kyc-btn");
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
