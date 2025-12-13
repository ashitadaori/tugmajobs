/**
 * KYC Completion Handler
 * Handles automatic detection of KYC completion and redirects users appropriately
 */

class KycCompletionHandler {
    constructor() {
        this.pollingInterval = null;
        this.maxPollingAttempts = 60; // 5 minutes at 5-second intervals
        this.currentAttempts = 0;
        this.pollingDelay = 5000; // 5 seconds
        this.userId = this.getUserId();
        this.sessionId = this.getSessionId();
        
        console.log('KYC Completion Handler initialized', {
            userId: this.userId,
            sessionId: this.sessionId
        });
    }
    
    getUserId() {
        // Try to get user ID from meta tag
        const metaTag = document.querySelector('meta[name="user-id"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Try to get from global variable
        if (window.currentUserId) {
            return window.currentUserId;
        }
        
        // Try to get from Laravel's global auth object
        if (window.Laravel && window.Laravel.user && window.Laravel.user.id) {
            return window.Laravel.user.id;
        }
        
        return null;
    }
    
    getSessionId() {
        // Try to get session ID from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const sessionId = urlParams.get('session_id');
        if (sessionId) {
            return sessionId;
        }
        
        // Try to get from meta tag
        const metaTag = document.querySelector('meta[name="kyc-session-id"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        return null;
    }
    
    startPolling() {
        if (!this.userId && !this.sessionId) {
            console.error('Cannot start KYC polling: No user ID or session ID available');
            return;
        }
        
        console.log('Starting KYC completion polling...');
        this.currentAttempts = 0;
        
        // Start polling immediately
        this.checkKycStatus();
        
        // Set up interval polling
        this.pollingInterval = setInterval(() => {
            this.checkKycStatus();
        }, this.pollingDelay);
    }
    
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            console.log('KYC polling stopped');
        }
    }
    
    async checkKycStatus() {
        this.currentAttempts++;
        
        console.log(`KYC status check attempt ${this.currentAttempts}/${this.maxPollingAttempts}`);
        
        // Stop polling if max attempts reached
        if (this.currentAttempts >= this.maxPollingAttempts) {
            console.log('Max polling attempts reached, stopping...');
            this.stopPolling();
            this.showTimeoutMessage();
            return;
        }
        
        try {
            const response = await this.makeStatusRequest();
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('KYC status response:', data);
            
            this.handleStatusResponse(data);
            
        } catch (error) {
            console.error('Error checking KYC status:', error);
            
            // Don't stop polling on network errors, just log them
            if (this.currentAttempts % 5 === 0) {
                console.warn(`Network error on attempt ${this.currentAttempts}, continuing...`);
            }
        }
    }
    
    async makeStatusRequest() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const requestBody = {};
        if (this.userId) {
            requestBody.user_id = this.userId;
        }
        if (this.sessionId) {
            requestBody.session_id = this.sessionId;
        }
        
        return fetch('/kyc/check-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(requestBody)
        });
    }
    
    handleStatusResponse(data) {
        const status = data.kyc_status || data.status;
        
        console.log('Current KYC status:', status);
        
        if (status === 'verified' || data.is_verified === true) {
            console.log('KYC verification completed! Redirecting...');
            this.stopPolling();
            this.handleVerificationComplete();
        } else if (status === 'failed') {
            console.log('KYC verification failed');
            this.stopPolling();
            this.handleVerificationFailed();
        } else if (status === 'expired') {
            console.log('KYC verification expired');
            this.stopPolling();
            this.handleVerificationExpired();
        } else {
            console.log('KYC still in progress, continuing to poll...');
        }
    }
    
    handleVerificationComplete() {
        console.log('🎉 KYC Completion Handler: Verification complete!');
        
        // Close any open modals immediately
        this.closeVerificationModal();
        this.closeKycModal();
        
        // Show success message popup
        this.showSuccessMessage();
        
        // Refresh page to show updated status after short delay
        setTimeout(() => {
            location.reload();
        }, 3000);
    }
    
    handleVerificationFailed() {
        this.showErrorMessage('Verification failed. Please try again.');
        this.closeVerificationModal();
    }
    
    handleVerificationExpired() {
        this.showErrorMessage('Verification session expired. Please start a new verification.');
        this.closeVerificationModal();
    }
    
    showSuccessMessage() {
        this.showMessage('success', 'Verification Complete!', 'Your identity has been successfully verified. Redirecting to your dashboard...');
    }
    
    showErrorMessage(message) {
        this.showMessage('error', 'Verification Error', message);
    }
    
    showTimeoutMessage() {
        this.showMessage('warning', 'Verification Timeout', 'Unable to automatically detect verification completion. Please refresh the page or check your status manually.');
    }
    
    showMessage(type, title, message) {
        // Remove any existing alerts
        const existingAlerts = document.querySelectorAll('.kyc-completion-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create alert
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-warning';
        
        const iconClass = type === 'success' ? 'fa-check-circle' : 
                         type === 'error' ? 'fa-exclamation-triangle' : 'fa-clock';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed kyc-completion-alert" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 350px;" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-remove after 10 seconds for non-success messages
        if (type !== 'success') {
            setTimeout(() => {
                const alert = document.querySelector('.kyc-completion-alert');
                if (alert) {
                    alert.remove();
                }
            }, 10000);
        }
    }
    
    closeVerificationModal() {
        // Close Bootstrap modal if it exists
        const modal = document.getElementById('kycVerificationModal');
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
        
        // Close any Didit iframe or popup
        const iframe = document.getElementById('kycIframe');
        if (iframe) {
            iframe.src = 'about:blank';
        }
    }
    
    closeKycModal() {
        // Close the main KYC modal if it exists
        const kycModal = document.getElementById('kycModal');
        if (kycModal) {
            const bsModal = bootstrap.Modal.getInstance(kycModal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }
    
    redirectToDashboard() {
        // Try to determine the correct dashboard URL
        let dashboardUrl = '/employer/dashboard'; // Default for employers
        
        // Check if we can determine user role from the page
        const userRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content');
        if (userRole === 'jobseeker') {
            dashboardUrl = '/account/dashboard';
        } else if (userRole === 'admin') {
            dashboardUrl = '/admin/dashboard';
        }
        
        console.log('Redirecting to dashboard:', dashboardUrl);
        window.location.href = dashboardUrl;
    }
}

// Global instance
window.kycCompletionHandler = null;

// Auto-start polling if we're on a page that might need it
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page that might need KYC completion detection
    const needsPolling = 
        window.location.pathname.includes('/kyc/') ||
        document.querySelector('[data-kyc-polling="true"]') ||
        document.getElementById('kycVerificationModal') ||
        document.querySelector('.kyc-verification-container');
    
    if (needsPolling) {
        console.log('KYC completion detection needed, initializing handler...');
        window.kycCompletionHandler = new KycCompletionHandler();
        
        // Start polling after a short delay to ensure page is fully loaded
        setTimeout(() => {
            window.kycCompletionHandler.startPolling();
        }, 1000);
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (window.kycCompletionHandler) {
        window.kycCompletionHandler.stopPolling();
    }
});

// Export for manual use
window.KycCompletionHandler = KycCompletionHandler;