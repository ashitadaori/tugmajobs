/**
 * KYC Inline Verification JavaScript - FIXED VERSION
 * Handles in-page KYC verification with improved error handling and debugging
 */

let verificationPolling = null;
let isVerificationInProgress = false;
let modalInstance = null;

// Enhanced logging function
function debugLog(message, data = null) {
    const timestamp = new Date().toLocaleTimeString();
    console.log(`[KYC ${timestamp}]`, message, data || '');
}

// Ensure CSRF token is available
function ensureCSRFToken() {
    let csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (!csrfToken) {
        console.warn('CSRF token meta tag not found, creating one...');
        csrfToken = document.createElement('meta');
        csrfToken.name = 'csrf-token';
        csrfToken.content = 'missing-token';
        document.head.appendChild(csrfToken);
    }
    
    const token = csrfToken.getAttribute('content');
    
    // Check if token looks valid (not empty or placeholder)
    if (!token || token === 'missing-token' || token.length < 10) {
        console.warn('Invalid CSRF token detected, attempting to refresh...');
        refreshCSRFToken();
        return csrfToken.getAttribute('content');
    }
    
    return token;
}

// Refresh CSRF token by making a request to get a new one
function refreshCSRFToken() {
    try {
        // Try to get a fresh token from the server
        fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'same-origin'
        }).then(() => {
            console.log('CSRF token refreshed');
        }).catch(error => {
            console.warn('Could not refresh CSRF token:', error);
        });
    } catch (error) {
        console.warn('Error refreshing CSRF token:', error);
    }
}

// Enhanced fetch with better error handling
function kycFetch(url, options = {}) {
    const csrfToken = ensureCSRFToken();
    
    // Default options
    const defaultOptions = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        mode: 'cors'
    };
    
    // Merge options
    const finalOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };
    
    console.log('KYC Fetch Request:', {
        url: url,
        options: finalOptions,
        origin: window.location.origin
    });
    
    return fetch(url, finalOptions)
        .then(response => {
            console.log('KYC Fetch Response:', {
                status: response.status,
                statusText: response.statusText,
                headers: Object.fromEntries(response.headers.entries()),
                url: response.url
            });
            
            return response;
        })
        .catch(error => {
            console.error('KYC Fetch Error:', error);
            throw error;
        });
}

function startInlineVerification(e) {
    // Show loading state - handle both passed event and global event
    const evt = e || window.event;
    const btn = evt && evt.target ? evt.target : document.activeElement;
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Starting...';
        btn.disabled = true;
    }
    
    console.log('🚀 Starting KYC verification...');
    console.log('Current URL:', window.location.href);
    console.log('Origin:', window.location.origin);
    
    // Set a maximum timeout for the entire verification process (30 minutes)
    const maxVerificationTime = 30 * 60 * 1000; // 30 minutes
    const verificationTimeout = setTimeout(() => {
        console.warn('⏰ KYC verification timed out after 30 minutes');
        if (isVerificationInProgress) {
            isVerificationInProgress = false;
            stopVerificationPolling();
            closeVerificationModal();
            showVerificationError('Verification timed out. Please try again.');
        }
    }, maxVerificationTime);
    
    // Start verification process with enhanced error handling
    kycFetch('/kyc/start')
    .then(response => {
        // Handle different response types
        const contentType = response.headers.get('content-type');
        
        if (!response.ok) {
            // Try to get error message from response
            return response.text().then(text => {
                console.error('Error response body:', text);
                let errorMessage = `HTTP error! status: ${response.status}`;
                
                // Handle specific error codes
                if (response.status === 419) {
                    errorMessage = 'Session expired. Please refresh the page and try again.';
                } else if (response.status === 401) {
                    errorMessage = 'Authentication required. Please login and try again.';
                } else if (response.status === 403) {
                    errorMessage = 'Access denied. Please check your permissions.';
                } else if (response.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                } else {
                    // Try to parse JSON error
                    try {
                        const errorData = JSON.parse(text);
                        if (errorData.error) {
                            errorMessage = errorData.error;
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // If not JSON, use a generic message
                        if (text.length > 0 && text.length < 500) {
                            errorMessage = text.replace(/<[^>]*>/g, ''); // Strip HTML tags
                        }
                    }
                }
                
                throw new Error(errorMessage);
            });
        }
        
        // Check if response is JSON
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // Handle non-JSON responses (like redirects)
            return response.text().then(text => {
                console.warn('Non-JSON response received:', text.substring(0, 200));
                throw new Error('Unexpected response format. Please try again.');
            });
        }
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.error) {
            // Show more helpful error message for in_progress status
            if (data.kyc_status === 'in_progress' && data.can_retry_at) {
                const retryTime = new Date(data.can_retry_at);
                const now = new Date();
                const minutesLeft = Math.ceil((retryTime - now) / (1000 * 60));
                
                if (minutesLeft > 0) {
                    showVerificationError(`${data.error} You can try again in ${minutesLeft} minutes.`);
                } else {
                    showVerificationError(`${data.error} Please refresh the page and try again.`);
                }
            } else {
                showVerificationError(data.error);
            }
        } else if (data.success && data.url) {
            console.log('Opening verification modal with URL:', data.url);
            openVerificationModal(data.url);
        } else {
            console.error('Invalid response structure:', data);
            showVerificationError('Invalid response from server. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error starting verification:', error);
        
        // Provide more specific error messages
        let errorMessage = error.message;
        
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            errorMessage = 'Network error: Unable to connect to the server. Please check your internet connection and try again.';
        } else if (error.name === 'TypeError' && error.message.includes('NetworkError')) {
            errorMessage = 'Network error: Please check your internet connection and try again.';
        } else if (error.message.includes('CORS')) {
            errorMessage = 'Security error: Please refresh the page and try again.';
        } else if (error.message.includes('Session expired')) {
            errorMessage = 'Session expired: Please refresh the page and try again.';
        }
        
        showVerificationError(errorMessage);
    })
    .finally(() => {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

function openVerificationModal(verificationUrl) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('kycVerificationModal');
    if (!modal) {
        modal = createVerificationModal();
    }

    // Set verification as in progress
    isVerificationInProgress = true;

    // Store the verification URL
    window.currentVerificationUrl = verificationUrl;

    // Show the verification panel (not iframe - most KYC providers block iframes)
    const loadingState = document.getElementById('kycLoadingState');
    const verificationPanel = document.getElementById('kycVerificationPanel');

    if (loadingState) loadingState.style.display = 'none';
    if (verificationPanel) verificationPanel.style.display = 'block';

    // Create modal instance with specific options
    modalInstance = new bootstrap.Modal(modal, {
        backdrop: 'static', // Prevent closing by clicking backdrop
        keyboard: false     // Prevent closing with escape key
    });

    // Add event listeners to prevent accidental closure
    modal.addEventListener('hide.bs.modal', function (e) {
        if (isVerificationInProgress) {
            // Show confirmation dialog before closing
            if (!confirm('Are you sure you want to close? Your verification may still be in progress.')) {
                e.preventDefault();
                return false;
            } else {
                // User confirmed, stop polling and reset state
                stopVerificationPolling();
                isVerificationInProgress = false;
            }
        }
    });

    // Show the modal
    modalInstance.show();

    console.log('KYC Modal opened with verification URL:', verificationUrl);

    // Start polling for verification status with a slight delay
    setTimeout(() => {
        startVerificationPolling();
    }, 2000); // Wait 2 seconds before starting to poll
}

function createVerificationModal() {
    const modalHtml = `
        <div class="modal fade" id="kycVerificationModal" tabindex="-1" aria-labelledby="kycVerificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header py-3" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none;">
                        <h5 class="modal-title" id="kycVerificationModalLabel">
                            <i class="fas fa-shield-alt me-2"></i>Identity Verification
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Loading State -->
                        <div id="kycLoadingState" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="mb-2">Preparing Verification...</h5>
                            <p class="text-muted mb-0">Please wait while we set up your session.</p>
                        </div>

                        <!-- Verification Panel - Main UI -->
                        <div id="kycVerificationPanel" style="display: none;">
                            <!-- Step indicator -->
                            <div class="text-center mb-4">
                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="fas fa-id-card" style="font-size: 2rem; color: white;"></i>
                                </div>
                                <h4 class="mt-3 mb-2">Complete Your Verification</h4>
                                <p class="text-muted">Click the button below to start. A new tab will open for the verification process.</p>
                            </div>

                            <!-- Requirements -->
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-2" style="background: #f8f9fa; border-radius: 8px;">
                                        <i class="fas fa-id-card text-primary me-2"></i>
                                        <small>Valid ID</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-2" style="background: #f8f9fa; border-radius: 8px;">
                                        <i class="fas fa-camera text-primary me-2"></i>
                                        <small>Camera Access</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-2" style="background: #f8f9fa; border-radius: 8px;">
                                        <i class="fas fa-lightbulb text-primary me-2"></i>
                                        <small>Good Lighting</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-2" style="background: #f8f9fa; border-radius: 8px;">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <small>~5 Minutes</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Start Button -->
                            <div class="d-grid mb-3">
                                <button type="button" class="btn btn-primary btn-lg" id="openVerificationBtn" onclick="openVerificationAndShowWaiting()" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; border-radius: 12px; padding: 14px;">
                                    <i class="fas fa-external-link-alt me-2"></i>
                                    Open Verification
                                </button>
                            </div>

                            <!-- Waiting Status (shown after clicking) -->
                            <div id="kycWaitingStatus" class="text-center p-4 mt-3" style="background: #f0f9ff; border-radius: 12px; border: 1px solid #bae6fd; display: none;">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                <span class="text-primary fw-semibold">Waiting for verification to complete...</span>
                                <p class="text-muted small mt-2 mb-0">Complete the verification in the new tab. This page will update automatically when done.</p>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="openVerificationWindow()">
                                        <i class="fas fa-redo me-1"></i>Reopen Verification
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="checkVerificationComplete()">
                                        <i class="fas fa-sync me-1"></i>Check Status
                                    </button>
                                </div>
                            </div>

                            <!-- Info note -->
                            <div class="alert alert-info mb-0 mt-3" style="border-radius: 12px; border: none; background: #f0f9ff;">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle me-2 mt-1 text-info"></i>
                                    <small>
                                        <strong>Keep this page open.</strong> It will automatically update once your verification is complete.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    return document.getElementById('kycVerificationModal');
}

// Function to open verification in new tab and show waiting status
function openVerificationAndShowWaiting() {
    if (window.currentVerificationUrl) {
        // Hide the start button and show waiting status
        const startBtn = document.getElementById('openVerificationBtn');
        const waitingStatus = document.getElementById('kycWaitingStatus');

        if (startBtn) startBtn.style.display = 'none';
        if (waitingStatus) waitingStatus.style.display = 'block';

        // Open the verification URL in a new tab
        window.open(window.currentVerificationUrl, '_blank');

        console.log('Verification tab opened, waiting for completion...');
    } else {
        showVerificationError('Verification URL not available. Please try again.');
    }
}

// Make function globally available
window.openVerificationAndShowWaiting = openVerificationAndShowWaiting;

// Function to open/reopen verification in a new tab
function openVerificationWindow() {
    if (window.currentVerificationUrl) {
        // Open the verification URL in a new tab
        window.open(window.currentVerificationUrl, '_blank');
        console.log('Verification window opened');
    } else {
        showVerificationError('Verification URL not available. Please try again.');
    }
}

// Make the function globally available
window.openVerificationWindow = openVerificationWindow;

function startVerificationPolling() {
    if (verificationPolling) {
        clearInterval(verificationPolling);
    }
    
    // Poll every 3 seconds
    verificationPolling = setInterval(checkVerificationComplete, 3000);
}

function stopVerificationPolling() {
    if (verificationPolling) {
        clearInterval(verificationPolling);
        verificationPolling = null;
        console.log('Verification polling stopped');
    }
}

function checkVerificationComplete() {
    // Skip checking if verification is not in progress
    if (!isVerificationInProgress) {
        console.log('Verification not in progress, skipping status check');
        return;
    }
    
    // Get user ID from meta tag or global variable
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 
                   window.currentUserId;
    
    if (!userId) {
        console.error('User ID not found for verification polling');
        return;
    }
    
    console.log('Checking verification status for user:', userId);
    
    // Use the enhanced fetch function
    kycFetch('/kyc/check-status', {
        body: JSON.stringify({
            user_id: userId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        console.log('Status check response:', data);
        
        if (data.kyc_status === 'verified') {
            // Verification completed!
            console.log('✅ KYC Verification completed successfully!');
            isVerificationInProgress = false;
            stopVerificationPolling();
            
            // Close the verification modal immediately
            closeVerificationModal();
            
            // Show success popup
            showVerificationSuccess('Your identity has been successfully verified.');
            
            // Update frontend status immediately
            if (window.updateKycStatusDisplay) {
                window.updateKycStatusDisplay('verified');
            }
            
            // Hide all KYC modals after showing success
            if (window.hideKycModals) {
                window.hideKycModals();
            }
            
            // Refresh the current page to show updated status (stays on the same page)
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } else if (data.kyc_status === 'failed' || data.kyc_status === 'declined' || data.kyc_status === 'rejected') {
            // Verification failed/declined!
            console.log('❌ KYC Verification failed/declined!');
            isVerificationInProgress = false;
            stopVerificationPolling();
            
            // Close the verification modal immediately
            closeVerificationModal();
            
            // Show failure popup with try again option
            showVerificationErrorWithRetry('Verification was declined. Please try the KYC verification again with clear, valid documents.');
            
        } else if (data.kyc_status === 'expired') {
            // Verification expired!
            console.log('⏰ KYC Verification session expired!');
            isVerificationInProgress = false;
            stopVerificationPolling();
            
            // Close the verification modal immediately
            closeVerificationModal();
            
            // Show expiration popup with try again option
            showVerificationErrorWithRetry('Verification session expired. Please try the KYC verification again.');
            
        } else if (data.kyc_status === 'cancelled' || data.kyc_status === 'canceled') {
            // Verification cancelled by user!
            console.log('🚫 KYC Verification cancelled by user!');
            isVerificationInProgress = false;
            stopVerificationPolling();
            
            // Close the verification modal immediately
            closeVerificationModal();
            
            // Show cancellation popup with try again option
            showVerificationErrorWithRetry('Verification was cancelled. You can try the KYC verification again when ready.');
        }
        // Continue polling for other statuses (in_progress, pending)
    })
    .catch(error => {
        console.error('Error checking verification status:', error);
        
        // Don't show error alerts for status checks, just log them
        // The polling will continue and might succeed on the next attempt
        if (error.message.includes('Failed to fetch') || error.name === 'TypeError') {
            console.warn('Network error during status check, will retry...');
        }
    });
}

function closeVerificationModal() {
    // Reset verification state
    isVerificationInProgress = false;
    stopVerificationPolling();

    const modal = document.getElementById('kycVerificationModal');
    if (modal) {
        // Remove the event listener to prevent confirmation dialog
        modal.removeEventListener('hide.bs.modal', arguments.callee);

        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }

        // Reset the modal UI state for next use
        const startBtn = document.getElementById('openVerificationBtn');
        const waitingStatus = document.getElementById('kycWaitingStatus');
        const verificationPanel = document.getElementById('kycVerificationPanel');

        if (startBtn) startBtn.style.display = 'block';
        if (waitingStatus) waitingStatus.style.display = 'none';
        if (verificationPanel) verificationPanel.style.display = 'none';
    }

    modalInstance = null;
    console.log('KYC Modal closed and state reset');
}

function showVerificationSuccess(message = null) {
    // Remove any existing alerts
    removeExistingAlerts();
    
    const defaultMessage = 'Your identity has been successfully verified.';
    const displayMessage = message || defaultMessage;
    
    // Create success toast/alert
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed kyc-alert" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Success!</strong> ${displayMessage}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
}

function showVerificationError(message) {
    // Remove any existing alerts
    removeExistingAlerts();
    
    // Create error toast/alert
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed kyc-alert" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Verification Error:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
}

function showVerificationErrorWithRetry(message) {
    // Remove any existing alerts
    removeExistingAlerts();
    
    // Create error alert with retry button
    const alertHtml = `
        <div class="alert alert-warning alert-dismissible fade show position-fixed kyc-alert" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 400px;" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>Verification Issue</strong>
                    <div class="mt-1">${message}</div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary btn-sm me-2" onclick="retryVerificationFromAlert()">
                            <i class="fas fa-redo me-1"></i>Try Again
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="alert">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-dismiss after 15 seconds if user doesn't interact
    setTimeout(() => {
        const alert = document.querySelector('.kyc-alert');
        if (alert) {
            const bootstrapAlert = new bootstrap.Alert(alert);
            bootstrapAlert.close();
        }
    }, 15000);
}

function removeExistingAlerts() {
    const existingAlerts = document.querySelectorAll('.kyc-alert');
    existingAlerts.forEach(alert => alert.remove());
}

// Modal-specific success function
function showVerificationSuccessInModal(message = null) {
    const defaultMessage = 'Your identity has been successfully verified!';
    const displayMessage = message || defaultMessage;
    
    // Update modal to show success state
    const modal = document.getElementById('kycVerificationModal');
    if (modal) {
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div class="text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-success mb-3">Verification Complete!</h4>
                <p class="text-muted mb-4">${displayMessage}</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-lg" onclick="closeModalAndReload()">
                        <i class="fas fa-check me-2"></i>Continue
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        `;
        
        // Update modal title
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-check-circle me-2 text-success"></i>Verification Successful';
        }
    }
}

// Modal-specific error function
function showVerificationErrorInModal(message) {
    const modal = document.getElementById('kycVerificationModal');
    if (modal) {
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div class="text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-warning mb-3">Verification Issue</h4>
                <p class="text-muted mb-4">${message}</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-lg" onclick="restartVerificationFromModal()">
                        <i class="fas fa-redo me-2"></i>Try Again
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        `;
        
        // Update modal title
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle me-2 text-warning"></i>Verification Status';
        }
    }
}

// Helper function to close modal and reload page
function closeModalAndReload() {
    closeVerificationModal();
    setTimeout(() => location.reload(), 500);
}

// Helper function to restart verification from within modal
function restartVerificationFromModal() {
    // Reset state
    isVerificationInProgress = false;
    stopVerificationPolling();
    
    // Close current modal
    closeVerificationModal();
    
    // Wait a moment then restart verification
    setTimeout(() => {
        // Create a fake event object since startInlineVerification expects an event
        const fakeEvent = { target: document.createElement('button') };
        fakeEvent.target.innerHTML = 'Try Again';
        fakeEvent.target.disabled = false;
        
        // Set the global event for the function to access
        window.event = fakeEvent;
        startInlineVerification();
    }, 500);
}

// Helper function to retry verification from alert popup
function retryVerificationFromAlert() {
    // Remove the alert first
    removeExistingAlerts();
    
    // Reset state
    isVerificationInProgress = false;
    stopVerificationPolling();
    
    // Wait a moment then restart verification
    setTimeout(() => {
        // Create a fake event object since startInlineVerification expects an event
        const fakeEvent = { target: document.createElement('button') };
        fakeEvent.target.innerHTML = 'Try Again';
        fakeEvent.target.disabled = false;
        
        // Set the global event for the function to access
        window.event = fakeEvent;
        startInlineVerification();
    }, 300);
}

// Clean up polling when page is unloaded
window.addEventListener('beforeunload', function() {
    if (verificationPolling) {
        clearInterval(verificationPolling);
    }
});

// Reset KYC verification function
function resetKycVerification() {
    // Show confirmation dialog
    if (!confirm('Are you sure you want to reset your KYC verification? This will cancel any verification in progress and you will need to start over.')) {
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Resetting...';
    btn.disabled = true;
    
    console.log('Resetting KYC verification...');
    
    // Reset verification process
    kycFetch('/kyc/reset')
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Reset error response:', text);
                let errorMessage = `HTTP error! status: ${response.status}`;
                
                try {
                    const errorData = JSON.parse(text);
                    if (errorData.error) {
                        errorMessage = errorData.error;
                    } else if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                } catch (e) {
                    if (text.length > 0 && text.length < 500) {
                        errorMessage = text.replace(/<[^>]*>/g, '');
                    }
                }
                
                throw new Error(errorMessage);
            });
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Unexpected response format');
        }
    })
    .then(data => {
        console.log('Reset response:', data);
        
        if (data.success) {
            showVerificationSuccess('KYC verification has been reset successfully. You can now start a new verification.');
            // Refresh the page after 2 seconds to show the updated status
            setTimeout(() => location.reload(), 2000);
        } else if (data.error) {
            showVerificationError(data.error);
        } else {
            showVerificationError('Unexpected response from server');
        }
    })
    .catch(error => {
        console.error('Error resetting verification:', error);
        showVerificationError(`Failed to reset verification: ${error.message}`);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Start verification polling function
function startVerificationPolling() {
    if (verificationPolling) {
        clearInterval(verificationPolling);
    }
    
    // Poll every 3 seconds
    verificationPolling = setInterval(checkVerificationComplete, 3000);
    
    // Also check immediately
    checkVerificationComplete();
}

// Manual status check function (for the Check Status button)
function checkVerificationStatus() {
    checkVerificationComplete();
}

// Make functions globally available
window.startInlineVerification = startInlineVerification;
window.resetKycVerification = resetKycVerification;
window.checkVerificationComplete = checkVerificationComplete;
window.checkVerificationStatus = checkVerificationStatus;
window.startVerificationPolling = startVerificationPolling;
window.showVerificationSuccessInModal = showVerificationSuccessInModal;
window.showVerificationErrorInModal = showVerificationErrorInModal;
window.closeModalAndReload = closeModalAndReload;
window.restartVerificationFromModal = restartVerificationFromModal;
window.retryVerificationFromAlert = retryVerificationFromAlert;
window.closeVerificationModal = closeVerificationModal;
