@extends('front.layouts.app')

@section('content')
<!-- Meta tags for KYC completion handler -->
<meta name="user-id" content="{{ Auth::id() }}">
<meta name="user-role" content="{{ Auth::user()->role }}">
@if(Auth::user()->kyc_session_id)
<meta name="kyc-session-id" content="{{ Auth::user()->kyc_session_id }}">
@endif

<style>
.kyc-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.kyc-header {
    text-align: center;
    margin-bottom: 2rem;
}

.kyc-header-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
}

.kyc-header-icon i {
    font-size: 2.5rem;
    color: white;
}

.kyc-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.kyc-header p {
    color: #64748b;
    font-size: 1rem;
}

.kyc-features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.kyc-feature {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
}

.kyc-feature i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.kyc-feature.quick i { color: #0ea5e9; }
.kyc-feature.secure i { color: #22c55e; }

.kyc-feature h5 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.kyc-feature p {
    font-size: 0.8125rem;
    color: #64748b;
    margin: 0;
}

/* Pending Review Card */
.pending-review-card {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border: 1px solid #7dd3fc;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    margin-bottom: 2rem;
}

.pending-review-card .icon {
    width: 64px;
    height: 64px;
    background: #0ea5e9;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.pending-review-card .icon i {
    font-size: 1.75rem;
    color: white;
}

.pending-review-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #0c4a6e;
    margin-bottom: 0.5rem;
}

.pending-review-card p {
    color: #0369a1;
    margin-bottom: 1.5rem;
}

.pending-review-card .btn-view {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #0ea5e9;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.pending-review-card .btn-view:hover {
    background: #0284c7;
    color: white;
    transform: translateY(-2px);
}

/* Verification Options */
.verification-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.verification-option {
    background: white;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.2s ease;
}

.verification-option:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.verification-option.primary {
    border-color: #6366f1;
}

.verification-option .option-header {
    padding: 1rem 1.25rem;
    font-weight: 600;
    color: white;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.verification-option.primary .option-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}

.verification-option.secondary .option-header {
    background: #64748b;
}

.verification-option .option-body {
    padding: 1.25rem;
}

.verification-option .option-body p {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 1rem;
}

.verification-option .option-body h6 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.verification-option .option-body ul {
    font-size: 0.8125rem;
    color: #64748b;
    padding-left: 1.25rem;
    margin-bottom: 1rem;
}

.verification-option .option-body ul li {
    margin-bottom: 0.25rem;
}

.verification-option .time-badge {
    background: #f1f5f9;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.verification-option .option-footer {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.verification-option .btn-action {
    width: 100%;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.verification-option.primary .btn-action {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
}

.verification-option.primary .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.verification-option.secondary .btn-action {
    background: #64748b;
    color: white;
}

.verification-option.secondary .btn-action:hover {
    background: #475569;
    transform: translateY(-2px);
}

.btn-action.verified {
    background: #22c55e !important;
    cursor: default;
}

/* Important Tips */
.important-tips {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}

.important-tips h5 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #92400e;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.important-tips ul {
    margin: 0;
    padding-left: 1.25rem;
}

.important-tips li {
    font-size: 0.875rem;
    color: #a16207;
    margin-bottom: 0.25rem;
}

/* Back Link */
.back-link {
    text-align: center;
}

.back-link a {
    color: #64748b;
    text-decoration: none;
    font-size: 0.9375rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.2s ease;
}

.back-link a:hover {
    color: #6366f1;
}

/* Verification Status (for in-progress) */
.verification-status-card {
    background: white;
    border: 2px solid #0ea5e9;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    margin-bottom: 2rem;
}

.verification-status-card .spinner {
    width: 48px;
    height: 48px;
    border: 3px solid #e0f2fe;
    border-top-color: #0ea5e9;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.verification-status-card h4 {
    color: #0369a1;
    margin-bottom: 0.5rem;
}

.verification-status-card p {
    color: #64748b;
    margin-bottom: 1rem;
}

.verification-status-card .btn-group {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.verification-status-card .btn-outline {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s ease;
}

.verification-status-card .btn-outline.secondary {
    background: white;
    border: 1px solid #cbd5e1;
    color: #475569;
}

.verification-status-card .btn-outline.secondary:hover {
    background: #f1f5f9;
}

.verification-status-card .btn-outline.warning {
    background: white;
    border: 1px solid #fbbf24;
    color: #b45309;
}

.verification-status-card .btn-outline.warning:hover {
    background: #fffbeb;
}

/* Responsive */
@media (max-width: 768px) {
    .kyc-features,
    .verification-options {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="kyc-container">
    <!-- Header -->
    <div class="kyc-header">
        <div class="kyc-header-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h1>Identity Verification</h1>
        <p>Verify your identity to access all platform features</p>
    </div>

    <!-- Alerts -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Features -->
    <div class="kyc-features">
        <div class="kyc-feature quick">
            <i class="fas fa-clock"></i>
            <h5>Quick Process</h5>
            <p>Verification takes only 2-5 minutes</p>
        </div>
        <div class="kyc-feature secure">
            <i class="fas fa-lock"></i>
            <h5>Secure & Private</h5>
            <p>Your data is encrypted and protected</p>
        </div>
    </div>

    @if(Auth::user()->kyc_status === 'verified')
        <!-- Already Verified -->
        <div class="pending-review-card" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-color: #6ee7b7;">
            <div class="icon" style="background: #22c55e;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 style="color: #166534;">Identity Verified</h3>
            <p style="color: #15803d;">Your identity has been successfully verified on {{ Auth::user()->kyc_verified_at ? Auth::user()->kyc_verified_at->format('M d, Y') : 'N/A' }}.</p>
            <a href="{{ Auth::user()->isEmployer() ? route('employer.dashboard') : route('account.dashboard') }}" class="btn-view" style="background: #22c55e;">
                <i class="fas fa-arrow-left"></i>Back to Dashboard
            </a>
        </div>

    @elseif(Auth::user()->kyc_status === 'pending_review')
        <!-- Pending Manual Review -->
        <div class="pending-review-card">
            <div class="icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <h3>Manual Review in Progress</h3>
            <p>Your documents have been submitted and are being reviewed by our team. This typically takes 1-3 business days. We'll notify you once the review is complete.</p>
            <a href="{{ route('kyc.manual.status') }}" class="btn-view">
                <i class="fas fa-eye"></i>View Submission Status
            </a>
        </div>

    @else
        <!-- In Progress Status -->
        @if(Auth::user()->kyc_status === 'in_progress')
            <div class="verification-status-card" id="verificationStatus">
                <div class="spinner"></div>
                <h4>Verification in Progress</h4>
                <p>Please complete the verification in the popup window or on your mobile device.</p>
                <div class="btn-group">
                    <button class="btn-outline secondary" onclick="window.checkVerificationComplete ? window.checkVerificationComplete() : location.reload()">
                        <i class="fas fa-sync"></i>Check Status
                    </button>
                    <button class="btn-outline warning" onclick="window.resetKycVerification ? window.resetKycVerification() : location.reload()">
                        <i class="fas fa-redo"></i>Start Over
                    </button>
                </div>
            </div>
        @endif

        <!-- Verification Options -->
        <div class="verification-options">
            <!-- Instant Verification -->
            <div class="verification-option primary">
                <div class="option-header">
                    <i class="fas fa-bolt"></i>Instant Verification
                </div>
                <div class="option-body">
                    <p>Automated verification using Didit - get results in minutes!</p>
                    <h6>Supported IDs:</h6>
                    <ul>
                        <li>Passport</li>
                        <li>Driver's License</li>
                        <li>National ID / Identity Card</li>
                        <li>Residence Permit</li>
                    </ul>
                    <div class="time-badge">
                        <i class="fas fa-clock"></i>Takes only 2-5 minutes
                    </div>
                </div>
                <div class="option-footer">
                    @if(Auth::user()->kyc_status === 'pending')
                        <button type="button" class="btn-action" onclick="startInlineVerification()">
                            <i class="fas fa-play"></i>Start Now
                        </button>
                    @elseif(in_array(Auth::user()->kyc_status, ['in_progress', 'failed', 'expired']))
                        <button type="button" class="btn-action" onclick="startInlineVerification()">
                            <i class="fas fa-redo"></i>
                            {{ Auth::user()->kyc_status === 'in_progress' ? 'Continue' : 'Try Again' }}
                        </button>
                    @endif
                </div>
            </div>

            <!-- Manual Verification -->
            <div class="verification-option secondary">
                <div class="option-header">
                    <i class="fas fa-file-upload"></i>Manual Verification
                </div>
                <div class="option-body">
                    <p>For Philippine IDs not supported by automated verification.</p>
                    <h6>Supported IDs:</h6>
                    <ul>
                        <li>PhilHealth ID / UMID</li>
                        <li>SSS ID / Postal ID</li>
                        <li>Voter's ID / PRC ID</li>
                        <li>And many more...</li>
                    </ul>
                    <div class="time-badge">
                        <i class="fas fa-user-clock"></i>Review takes 1-3 business days
                    </div>
                </div>
                <div class="option-footer">
                    <a href="{{ route('kyc.manual.form') }}" class="btn-action">
                        <i class="fas fa-upload"></i>Upload Documents
                    </a>
                </div>
            </div>
        </div>

        <!-- Failed/Expired Status Message -->
        @if(in_array(Auth::user()->kyc_status, ['failed', 'expired']))
            <div class="alert alert-{{ Auth::user()->kyc_status === 'failed' ? 'danger' : 'warning' }} mb-4">
                <strong>
                    <i class="fas fa-{{ Auth::user()->kyc_status === 'failed' ? 'times-circle' : 'clock' }} me-2"></i>
                    {{ Auth::user()->kyc_status === 'failed' ? 'Previous Verification Failed' : 'Session Expired' }}
                </strong>
                <p class="mb-0 mt-2">
                    @if(Auth::user()->kyc_status === 'failed')
                        Your previous attempt was unsuccessful. Please try again with clear, well-lit photos of your documents.
                    @else
                        Your previous session timed out. Please start a new verification.
                    @endif
                </p>
            </div>
        @endif
    @endif

    <!-- Important Tips (only show when not verified/pending_review) -->
    @if(!in_array(Auth::user()->kyc_status, ['verified', 'pending_review']))
        <div class="important-tips">
            <h5><i class="fas fa-lightbulb"></i>Important Tips</h5>
            <ul>
                <li>Make sure your documents are not expired</li>
                <li>Ensure photos are clear and readable</li>
                <li>Complete the process in one session</li>
            </ul>
        </div>
    @endif

    <!-- Back Link -->
    <div class="back-link">
        @if(Auth::user()->isEmployer())
            <a href="{{ route('employer.dashboard') }}">
                <i class="fas fa-arrow-left"></i>Back to Dashboard
            </a>
        @else
            <a href="{{ route('account.dashboard') }}">
                <i class="fas fa-arrow-left"></i>Back to Dashboard
            </a>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<!-- Include the KYC inline verification script -->
<script src="{{ asset('assets/js/kyc-inline-verification.js') }}"></script>
<!-- Include the KYC completion handler -->
<script src="{{ asset('assets/js/kyc-completion-handler.js') }}"></script>

<script>
// Auto-start polling if user is already in verification process
@if(Auth::user()->kyc_status === 'in_progress')
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof startVerificationPolling === 'function') {
            startVerificationPolling();
        }
    });
@endif
</script>
@endpush
