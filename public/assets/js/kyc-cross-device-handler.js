/**
 * KYC Cross-Device Handler
 * Handles KYC verification when user scans QR code on mobile device
 * but needs to redirect the original desktop browser
 */

class KycCrossDeviceHandler {
    constructor() {
        this.pollingInterval = null;
        this.maxPollingAttempts = 120; // 10 minutes at 5-second intervals
        this.currentAttempts = 0;
        this.pollingDelay = 5000; // 5 seconds
        this.userId = this.getUserId();
        this.sessionId = this.getSessionId();
        this.isDesktopSession = this.isDesktopDevice();
        this.isMobileRedirect = this.isMobileRedirectPage();

        console.log('KYC Cross-Device Handler initialized', {
            userId: this.userId,
            sessionId: this.sessionId,
            isDesktop: this.isDesktopSession,
            isMobileRedirect: this.isMobileRedirect,
            userAgent: navigator.userAgent
        });

        this.init();
    }

    init() {
        if (this.isMobileRedirect) {
            // This is a mobile device that was redirected after verification
            this.handleMobileRedirect();
        } else if (this.isDesktopSession && this.userId) {
            // This is the original desktop session, start polling
            this.startDesktopPolling();
        }
    }

    getUserId() {
        const metaTag = document.querySelector('meta[name="user-id"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }

        if (window.currentUserId) {
            return window.currentUserId;
        }

        // Try to get from URL parameters (for mobile redirects)
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id');
        if (userId) {
            return userId;
        }

        return null;
    }

    getSessionId() {
        const urlParams = new URLSearchParams(window.location.search);
        const sessionId = urlParams.get('session_id');
        if (sessionId) {
            return sessionId;
        }

        const metaTag = document.querySelector('meta[name="kyc-session-id"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }

        return null;
    }

    isDesktopDevice() {
        const userAgent = navigator.userAgent.toLowerCase();
        const mobileKeywords = ['mobile', 'android', 'iphone', 'ipad', 'ipod', 'blackberry', 'windows phone'];
        return !mobileKeywords.some(keyword => userAgent.includes(keyword));
    }

    isMobileRedirectPage() {
        // Check if this is a mobile redirect from Didit
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const sessionId = urlParams.get('session_id');
        const isMobile = !this.isDesktopDevice();

        // If we have KYC parameters and we're on mobile, this is likely a redirect
        return isMobile && (status || sessionId) && window.location.pathname.includes('/kyc/');
    }

    handleMobileRedirect() {
        console.log('Handling mobile redirect after KYC completion');

        // Show a mobile-friendly success page
        this.showMobileSuccessPage();

        // Notify the server that verification was completed on mobile
        this.notifyVerificationComplete();
    }

    showMobileSuccessPage() {
        // Create a mobile-friendly success page
        const body = document.body;
        body.innerHTML = `
            <div class="container-fluid vh-100 d-flex align-items-center justify-content-center bg-light">
                <div class="text-center p-4">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-success mb-3">Verification Complete!</h2>
                    <p class="lead mb-4">Your identity has been successfully verified.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Next Steps:</strong><br>
                        Return to your computer browser where you started the verification.
                        The page will automatically update and redirect you to your dashboard.
                    </div>
                    <div class="mt-4">
                        <button onclick="window.close()" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-2"></i>Close This Window
                        </button>
                        <a href="/" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            You can safely close this window and return to your computer.
                        </small>
                    </div>
                </div>
            </div>
        `;

        // Add some basic styling
        const style = document.createElement('style');
        style.textContent = `
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                margin: 0;
                padding: 0;
            }
            .container-fluid {
                min-height: 100vh;
            }
        `;
        document.head.appendChild(style);
    }

    async notifyVerificationComplete() {
        if (!this.sessionId && !this.userId) {
            console.error('Cannot notify verification complete: No session ID or user ID');
            return;
        }

        try {
            const response = await fetch('/kyc/mobile-completion-notify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    session_id: this.sessionId,
                    user_id: this.userId,
                    mobile_completion: true,
                    user_agent: navigator.userAgent,
                    timestamp: new Date().toISOString()
                })
            });

            if (response.ok) {
                console.log('Successfully notified server of mobile completion');
            } else {
                console.warn('Failed to notify server of mobile completion');
            }
        } catch (error) {
            console.error('Error notifying server of mobile completion:', error);
        }
    }

    startDesktopPolling() {
        if (!this.userId) {
            console.error('Cannot start desktop polling: No user ID available');
            return;
        }

        console.log('Starting desktop polling for KYC completion...');
        this.currentAttempts = 0;

        // Show polling status
        this.showDesktopPollingStatus();

        // Start polling immediately
        this.checkKycStatusDesktop();

        // Set up interval polling
        this.pollingInterval = setInterval(() => {
            this.checkKycStatusDesktop();
        }, this.pollingDelay);
    }

    showDesktopPollingStatus() {
        // Status display disabled as per user request
        console.log('Monitoring verification status in background...');
    }

    async checkKycStatusDesktop() {
        this.currentAttempts++;

        console.log(`Desktop KYC status check attempt ${this.currentAttempts}/${this.maxPollingAttempts}`);

        // Stop polling if max attempts reached
        if (this.currentAttempts >= this.maxPollingAttempts) {
            console.log('Max polling attempts reached, stopping...');
            this.stopDesktopPolling();
            this.showDesktopTimeout();
            return;
        }

        try {
            const response = await fetch('/kyc/check-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    user_id: this.userId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Desktop KYC status response:', data);

            this.handleDesktopStatusResponse(data);

        } catch (error) {
            console.error('Error checking KYC status on desktop:', error);

            // Don't stop polling on network errors
            if (this.currentAttempts % 10 === 0) {
                console.warn(`Network error on attempt ${this.currentAttempts}, continuing...`);
            }
        }
    }

    handleDesktopStatusResponse(data) {
        const status = data.kyc_status || data.status;

        if (status === 'verified' || data.is_verified === true) {
            console.log('KYC verification completed on desktop! Redirecting...');
            this.stopDesktopPolling();
            this.handleDesktopVerificationComplete();
        } else if (status === 'failed') {
            console.log('KYC verification failed');
            this.stopDesktopPolling();
            this.handleDesktopVerificationFailed();
        } else if (status === 'expired') {
            console.log('KYC verification expired');
            this.stopDesktopPolling();
            this.handleDesktopVerificationExpired();
        }
        // Continue polling for other statuses
    }

    handleDesktopVerificationComplete() {
        // Update status display
        this.updateDesktopStatus('success', 'Verification Complete!', 'Redirecting to your dashboard...');

        // Redirect after a short delay
        setTimeout(() => {
            this.redirectToDashboard();
        }, 2000);
    }

    handleDesktopVerificationFailed() {
        this.updateDesktopStatus('danger', 'Verification Failed', 'Please try again with clear documents.');
    }

    handleDesktopVerificationExpired() {
        this.updateDesktopStatus('warning', 'Verification Expired', 'Please start a new verification process.');
    }

    updateDesktopStatus(type, title, message) {
        const statusContainer = document.getElementById('kyc-cross-device-status');
        if (statusContainer) {
            const alertClass = `alert-${type}`;
            const iconClass = type === 'success' ? 'fa-check-circle' :
                type === 'danger' ? 'fa-exclamation-triangle' : 'fa-clock';

            statusContainer.className = `alert ${alertClass} mt-3`;
            statusContainer.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas ${iconClass} me-3"></i>
                    <div>
                        <strong>${title}</strong><br>
                        <small>${message}</small>
                    </div>
                </div>
            `;
        }
    }

    stopDesktopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            console.log('Desktop KYC polling stopped');
        }
    }

    showDesktopTimeout() {
        this.updateDesktopStatus('warning', 'Verification Timeout', 'Unable to automatically detect completion. Please refresh the page or check your status manually.');
    }

    redirectToDashboard() {
        // Determine the correct dashboard URL
        const userRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content');
        let dashboardUrl = '/employer/dashboard'; // Default for employers

        if (userRole === 'jobseeker') {
            dashboardUrl = '/account/dashboard';
        } else if (userRole === 'admin') {
            dashboardUrl = '/admin/dashboard';
        }

        console.log('Redirecting to dashboard:', dashboardUrl);
        window.location.href = dashboardUrl;
    }

    getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }
}

// Initialize the cross-device handler
document.addEventListener('DOMContentLoaded', function () {
    // Check if we need cross-device handling
    // Only activate on KYC-specific pages, not on dashboard or other pages
    const isKycPage = window.location.pathname.includes('/kyc/');
    const hasExplicitTrigger = document.querySelector('[data-kyc-cross-device="true"]');

    // Don't auto-initialize on dashboard or other non-KYC pages
    // The banner is too intrusive for regular browsing
    const needsCrossDeviceHandling = isKycPage || hasExplicitTrigger;

    if (needsCrossDeviceHandling) {
        console.log('Initializing KYC cross-device handler...');
        window.kycCrossDeviceHandler = new KycCrossDeviceHandler();
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function () {
    if (window.kycCrossDeviceHandler) {
        window.kycCrossDeviceHandler.stopDesktopPolling();
    }
});

// Export for manual use
window.KycCrossDeviceHandler = KycCrossDeviceHandler;