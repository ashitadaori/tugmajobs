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
        debugLog('CSRF token meta tag not found, creating one...');
        csrfToken = document.createElement('meta');
        csrfToken.name = 'csrf-token';
        csrfToken.content = 'missing-token';
        document.head.appendChild(csrfToken);
    }
    
    const token = csrfToken.getAttribute('content');
    
    // Check if token looks valid (not empty or placeholder)
    if (!token || token === 'missing-token' || token.length < 10) {
        debugLog('Invalid CSRF token detected', token);
        return token; // Return whatever we have
    }
    
    return token;
}

// Enhanced fetch with better error handling and debugging
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
        credentials: 'same-origin'
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
    
    debugLog('KYC Fetch Request', {
        url: url,
        method: finalOptions.method,
        headers: finalOptions.headers,
        origin: window.location.origin
    });
    
    return fetch(url, finalOptions)
        .then(response => {
            debugLog('KYC Fetch Response', {
                status: response.status,
                statusText: response.statusText,
                url: response.url,
                ok: response.ok
            });
            
            return response;
        })
        .catch(error => {
            debugLog('KYC Fetch Error', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            throw error;
        });
}

function startInlineVerification() {
    debugLog('Starting KYC verification...');
    
    // Find the button that triggered this
    const btn = event ? event.target : document.querySelector('[onclick*="startInlineVerification"]');
    let originalText = '';
    
    if (btn) {
        originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Starting...';
        btn.disabled = true;
    }
    
    // Start verification process with enhanced error handling
    kycFetch('/kyc/start')
    .then(response => {
        // Handle different response types
        const contentType = response.headers.get('content-type');
        debugLog('Response content type', contentType);
        
        if (!response.ok) {
            // Try to get error message from response
            return response.text().then(text => {
                debugLog('Error response body', text);
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
                debugLog('Non-JSON response received', text.substring(0, 200));
                throw new Error('Unexpected response format. Please try again.');
            });
        }
    })
    .then(data => {
        debugLog('Response data', data);
        
        if (data.error) {
            debugLog('Server returned error', data.error);
            showVerificationError(data.error);
        } else if (data.success && data.url) {
            debugLog('Opening verification modal with URL', data.url);
            openVerificationModal(data.url);
        } else {
            debugLog('Invalid response structure', data);
            showVerificationError('Invalid response from server. Please try again.');
        }
    })
    .catch(error => {
        debugLog('Error starting verification', error);
        
        // Provide more specific error messages
        let errorMessage = error.message;
        
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            errorMessage = 'Network error: Unable to connect to the server. Please check your internet connection and try again.';
        } else if (error.name === 'TypeError' && error.message.includes('NetworkError')) {
            errorMessage = 'Network error: Please check your internet connection and try again.';
        } else if (error.message.includes('CORS')) {
            errorMessage = 'Security error: Please refresh the page and try again.';
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
    debugLog('Opening verification modal', verificationUrl);
    
    // Create modal if it doesn't exist
    let modal = document.getElementById('kycVerificationModal');
    if (!modal) {
        modal = createVerificationModal();
    }
    
    // Set verification as in progress
    isVerificationInProgress = true;
    
    // Reset modal state
    const loadingState = document.getElementById('kycLoadingState');
    const iframe = document.getElementById('kycIframe');
    
    if (loadingState) loadingState.style.display = 'block';
    if (iframe) iframe.style.display = 'none';
    
    // Set the iframe source
    if (iframe) {
        iframe.src = verificationUrl;
    }
    
    // Create modal instance with specific options
    modalInstance = new bootstrap.Modal(modal, {
        backdrop: 'static', // Prevent closing by clicking backdrop
        keyboard: false     // Prevent closing with escape key
    });
    
    // Add simplified event listener for modal close
    modal.addEventListener('hide.bs.modal', function (e) {
        debugLog('Modal hide event triggered', { isVerificationInProgress });
        
        if (isVerificationInProgress) {
            // Ask for confirmation only if verification is really in progress
            const shouldClose = confirm('Are you sure you want to close the verification? This will cancel your verification process.');
            if (!shouldClose) {
                e.preventDefault();
                return false;
            } else {
                // User confirmed, stop polling and reset state
                stopVerificationPolling();
                isVerificationInProgress = false;
                debugLog('User confirmed modal close, stopping verification');
            }
        }
    });
    
    // Show the modal
    modalInstance.show();
    
    debugLog('KYC Modal opened, starting verification polling in 3 seconds...');
    
    // Start polling for verification status with a delay
    setTimeout(() => {
        startVerificationPolling();
    }, 3000); // Wait 3 seconds before starting to poll
}

function createVerificationModal() {
    debugLog('Creating verification modal');
    
    const modalHtml = `
        <div class="modal fade" id="kycVerificationModal" tabindex="-1" aria-labelledby="kycVerificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="kycVerificationModalLabel">
                            <i class="fas fa-shield-alt me-2"></i>Identity Verification
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="kycLoadingState" class="text-center p-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5>Loading Verification...</h5>
                            <p class="text-muted">Please wait while we prepare your verification session.</p>
                        </div>
                        <iframe id="kycIframe" src="" style="width: 100%; height: 500px; border: none; display: none;" 
                                onload="hideKycLoading()"></iframe>
                    </div>
                    <div class="modal-footer">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Complete the verification process in the window above. This page will automatically update when finished.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    return document.getElementById('kycVerificationModal');
}

function hideKycLoading() {
    debugLog('Hiding loading state, showing iframe');
    
    const loadingState = document.getElementById('kycLoadingState');
    const iframe = document.getElementById('kycIframe');
    
    if (loadingState) loadingState.style.display = 'none';
    if (iframe) iframe.style.display = 'block';
}

function startVerificationPolling() {
    if (verificationPolling) {
        clearInterval(verificationPolling);
    }
    
    debugLog('Starting verification polling (every 3 seconds)');
    
    // Poll every 3 seconds
    verificationPolling = setInterval(checkVerificationComplete, 3000);
    
    // Also check immediately
    checkVerificationComplete();
}

function stopVerificationPolling() {
    if (verificationPolling) {
        clearInterval(verificationPolling);
        verificationPolling = null;
        debugLog('Verification polling stopped');
    }
}

function checkVerificationComplete() {
    // Skip checking if verification is not in progress
    if (!isVerificationInProgress) {
        debugLog('Verification not in progress, skipping status check');
        return;
    }
    
    // Get user ID from meta tag or global variable
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 
                   window.currentUserId;
    
    if (!userId) {
        debugLog('User ID not found for verification polling');
        return;
    }
    
    debugLog('Checking verification status for user', userId);
    
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
        debugLog('Status check response', data);
        
        if (data.kyc_status === 'verified') {
            // Verification completed!
            debugLog('Verification completed successfully!');
            isVerificationInProgress = false;
            stopVerificationPolling();
            closeVerificationModal();
            showVerificationSuccess();
            // Refresh the page to update UI
            setTimeout(() => location.reload(), 2000);
        } else if (data.kyc_status === 'failed') {
            debugLog('Verification failed');
            isVerificationInProgress = false;
            stopVerificationPolling();
            closeVerificationModal();
            showVerificationError('Verification failed. Please try again.');
        } else if (data.kyc_status === 'expired') {
            debugLog('Verification expired');
            isVerificationInProgress = false;
            stopVerificationPolling();
            closeVerificationModal();
            showVerificationError('Verification session expired. Please try again.');
        } else {
            debugLog('Verification still in progress', data.kyc_status);
        }
        // Continue polling for other statuses (in_progress, pending)
    })
    .catch(error => {
        debugLog('Error checking verification status', error);
        
        // Don't show error alerts for status checks, just log them
        // The polling will continue and might succeed on the next attempt
    });
}

function closeVerificationModal() {
    debugLog('Closing verification modal');
    
    // Reset verification state
    isVerificationInProgress = false;
    stopVerificationPolling();
    
    const modal = document.getElementById('kycVerificationModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
        
        // Clear iframe src to stop any ongoing processes
        const iframe = modal.querySelector('#kycIframe');
        if (iframe) {
            iframe.src = 'about:blank';
        }
    }
    
    modalInstance = null;
    debugLog('KYC Modal closed and state reset');
}

function showVerificationSuccess(message = null) {
    debugLog('Showing verification success');
    
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
    debugLog('Showing verification error', message);
    
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

function removeExistingAlerts() {
    const existingAlerts = document.querySelectorAll('.kyc-alert');
    existingAlerts.forEach(alert => alert.remove());
}

// Reset KYC verification function
function resetKycVerification() {
    debugLog('Reset KYC verification requested');
    
    // Show confirmation dialog
    if (!confirm('Are you sure you want to reset your KYC verification? This will cancel any verification in progress and you will need to start over.')) {
        return;
    }
    
    // Find the button that triggered this
    const btn = event ? event.target : null;
    let originalText = '';
    
    if (btn) {
        originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Resetting...';
        btn.disabled = true;
    }
    
    // Reset verification process
    kycFetch('/kyc/reset')
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                debugLog('Reset error response', text);
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
        debugLog('Reset response', data);
        
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
        debugLog('Error resetting verification', error);
        showVerificationError(`Failed to reset verification: ${error.message}`);
    })
    .finally(() => {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

// Clean up polling when page is unloaded
window.addEventListener('beforeunload', function() {
    if (verificationPolling) {
        clearInterval(verificationPolling);
    }
});

// Manual status check function (for the Check Status button)
function checkVerificationStatus() {
    debugLog('Manual status check requested');
    checkVerificationComplete();
}

// Make functions globally available
window.startInlineVerification = startInlineVerification;
window.hideKycLoading = hideKycLoading;
window.resetKycVerification = resetKycVerification;
window.checkVerificationComplete = checkVerificationComplete;
window.checkVerificationStatus = checkVerificationStatus;
window.startVerificationPolling = startVerificationPolling;

// Initialize debugging on page load
document.addEventListener('DOMContentLoaded', function() {
    debugLog('KYC Inline Verification initialized');
    debugLog('Current URL', window.location.href);
    debugLog('CSRF Token', ensureCSRFToken());
    debugLog('User ID from meta tag', document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 'Not found');
});
