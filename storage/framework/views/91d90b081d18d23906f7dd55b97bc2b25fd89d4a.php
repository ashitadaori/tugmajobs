
<div class="modal fade" id="verificationAlertModal" tabindex="-1" aria-labelledby="verificationAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title" id="verificationAlertModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Verification Required
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <i class="bi bi-lock-fill text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Complete Your Verification</h6>
                <p class="text-muted mb-4" id="verificationMessage">
                    To ensure the security and authenticity of job postings, you must complete the verification process before posting jobs.
                </p>
                
                <div class="d-flex flex-column gap-2" id="verificationActions">
                    <!-- Actions will be populated based on verification status -->
                </div>
                
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="bi bi-info-circle me-2"></i>
                        This process typically takes just a few minutes to complete.
                    </small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showVerificationAlert(message, status = 'unknown') {
    // Update the modal message
    document.getElementById('verificationMessage').textContent = message;
    
    // Clear existing actions
    const actionsContainer = document.getElementById('verificationActions');
    actionsContainer.innerHTML = '';
    
    // Add appropriate action buttons based on status
    if (status === 'kyc_pending') {
        actionsContainer.innerHTML = `
            <button type="button" class="btn btn-warning" onclick="handleKYCVerification()">
                <i class="bi bi-shield-check me-2"></i>Start KYC Verification
            </button>
            <small class="text-muted text-center">Complete your identity verification to continue</small>
        `;
    } else if (status === 'documents_pending') {
        actionsContainer.innerHTML = `
            <a href="${window.employerDocumentsUrl || '/employer/documents'}" class="btn btn-primary">
                <i class="bi bi-file-earmark-text me-2"></i>Submit Required Documents
            </a>
            <small class="text-muted text-center">Upload your business registration and other required documents</small>
        `;
    } else {
        actionsContainer.innerHTML = `
            <div class="text-center">
                <small class="text-muted">Please contact support if you need assistance with verification.</small>
            </div>
        `;
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('verificationAlertModal'));
    modal.show();
}

function handleKYCVerification() {
    // Close the alert modal first
    const modal = bootstrap.Modal.getInstance(document.getElementById('verificationAlertModal'));
    if (modal) {
        modal.hide();
    }
    
    // Start inline KYC verification if available
    if (window.startInlineVerification && typeof window.startInlineVerification === 'function') {
        window.startInlineVerification();
    } else {
        alert('KYC verification is not available at the moment. Please try again later or contact support.');
    }
}

// Make the function globally available
window.showVerificationAlert = showVerificationAlert;
</script>

<style>
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
    border-radius: 12px 12px 0 0;
}

#verificationAlertModal .btn {
    border-radius: 8px;
    font-weight: 500;
}

#verificationAlertModal .alert {
    background-color: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 8px;
}
</style>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/verification-alert-modal.blade.php ENDPATH**/ ?>