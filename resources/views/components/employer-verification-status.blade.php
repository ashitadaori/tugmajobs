@php
    $user = Auth::user();
    $isKycVerified = $user->isKycVerified();
    $hasRequiredDocs = $user->hasRequiredDocumentsApproved();
    $canPostJobs = $user->canPostJobs();
    $verificationStatus = $user->getEmployerVerificationStatus();
@endphp

@if(!$canPostJobs)
<div class="verification-status-card mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <h6 class="mb-0">Complete Verification to Post Jobs</h6>
            </div>
        </div>
        <div class="card-body">
            <div class="verification-steps">
                <!-- KYC Verification Step -->
                <div class="step d-flex align-items-center mb-3">
                    <div class="step-icon me-3">
                        @if($isKycVerified)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-circle text-muted"></i>
                        @endif
                    </div>
                    <div class="step-content flex-grow-1">
                        <div class="step-title fw-semibold">
                            Identity Verification (KYC)
                            @if($isKycVerified)
                                <span class="badge bg-success ms-2">Complete</span>
                            @else
                                <span class="badge bg-warning ms-2">Required</span>
                            @endif
                        </div>
                        <div class="step-description text-muted small">
                            Verify your identity using government-issued ID
                        </div>
                    </div>
                    @if(!$isKycVerified)
                        <div class="step-action">
                            <button class="btn btn-sm btn-outline-primary" onclick="window.startInlineVerification ? window.startInlineVerification() : alert('KYC verification not available')">
                                <i class="bi bi-shield-check me-1"></i>Start KYC
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Document Submission Step -->
                <div class="step d-flex align-items-center">
                    <div class="step-icon me-3">
                        @if($hasRequiredDocs)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @elseif($isKycVerified)
                            <i class="bi bi-circle text-muted"></i>
                        @else
                            <i class="bi bi-circle text-secondary opacity-50"></i>
                        @endif
                    </div>
                    <div class="step-content flex-grow-1">
                        <div class="step-title fw-semibold {{ !$isKycVerified ? 'text-muted' : '' }}">
                            Business Documents
                            @if($hasRequiredDocs)
                                <span class="badge bg-success ms-2">Complete</span>
                            @elseif($isKycVerified)
                                <span class="badge bg-warning ms-2">Required</span>
                            @else
                                <span class="badge bg-secondary ms-2">Locked</span>
                            @endif
                        </div>
                        <div class="step-description text-muted small">
                            Submit business registration and tax documents
                        </div>
                    </div>
                    @if($isKycVerified && !$hasRequiredDocs)
                        <div class="step-action">
                            <a href="{{ route('employer.documents.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-1"></i>Submit Documents
                            </a>
                        </div>
                    @elseif(!$isKycVerified)
                        <div class="step-action">
                            <button class="btn btn-sm btn-secondary" disabled>
                                <i class="bi bi-lock me-1"></i>Locked
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            @if($canPostJobs)
                <div class="alert alert-success mt-3 mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Verification Complete!</strong> You can now post jobs and manage applications.
                </div>
            @else
                <div class="progress mt-3">
                    @php
                        $progress = 0;
                        if ($isKycVerified) $progress += 50;
                        if ($hasRequiredDocs) $progress += 50;
                    @endphp
                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">{{ $progress }}% Complete</small>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<style>
.verification-status-card .step-icon {
    font-size: 1.5rem;
    width: 2rem;
    text-align: center;
}

.verification-status-card .step-title {
    margin-bottom: 0.25rem;
}

.verification-status-card .progress {
    height: 8px;
    border-radius: 4px;
}

.verification-status-card .card-header h6 {
    color: #664d03;
}
</style>
