@extends('front.layouts.app')

@section('content')
<style>
/* Manual KYC Upload Styles */
.manual-kyc-container {
    max-width: 800px;
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
    max-width: 500px;
    margin: 0 auto;
}

.kyc-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.kyc-card-body {
    padding: 2rem;
}

/* Info Banner */
.info-banner {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 2rem;
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.info-banner-icon {
    width: 40px;
    height: 40px;
    background: #3b82f6;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-banner-icon i {
    color: white;
    font-size: 1.125rem;
}

.info-banner-content h4 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 0.25rem;
}

.info-banner-content p {
    font-size: 0.875rem;
    color: #3b82f6;
    margin: 0;
}

/* Pending Banner */
.pending-banner {
    background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
    border: 1px solid #fde047;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.pending-banner h4 {
    color: #854d0e;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pending-banner p {
    color: #a16207;
    margin-bottom: 1rem;
}

.pending-banner .btn-group {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Form Sections */
.form-section {
    margin-bottom: 2rem;
}

.form-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-title .step-number {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
}

/* Custom Select */
.custom-select-wrapper {
    position: relative;
}

.custom-select-wrapper select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    color: #1e293b;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
    appearance: none;
    -webkit-appearance: none;
}

.custom-select-wrapper select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.custom-select-wrapper::after {
    content: '\f078';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    pointer-events: none;
}

/* Custom Input */
.custom-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    color: #1e293b;
    transition: all 0.2s ease;
}

.custom-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.custom-input::placeholder {
    color: #94a3b8;
}

/* Upload Cards */
.upload-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.upload-card {
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    background: #f8fafc;
}

.upload-card:hover {
    border-color: #6366f1;
    background: #f5f3ff;
}

.upload-card.has-file {
    border-color: #22c55e;
    border-style: solid;
    background: #f0fdf4;
}

.upload-card input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-card-icon {
    width: 48px;
    height: 48px;
    background: #e2e8f0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    transition: all 0.2s ease;
}

.upload-card:hover .upload-card-icon {
    background: #6366f1;
}

.upload-card:hover .upload-card-icon i {
    color: white;
}

.upload-card.has-file .upload-card-icon {
    background: #22c55e;
}

.upload-card.has-file .upload-card-icon i {
    color: white;
}

.upload-card-icon i {
    font-size: 1.25rem;
    color: #64748b;
    transition: all 0.2s ease;
}

.upload-card-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.upload-card-subtitle {
    font-size: 0.8125rem;
    color: #64748b;
}

.upload-card .required-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: #ef4444;
    color: white;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    text-transform: uppercase;
}

.upload-card .optional-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: #94a3b8;
    color: white;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    text-transform: uppercase;
}

.upload-preview {
    margin-top: 1rem;
    display: none;
}

.upload-preview img {
    max-width: 100%;
    max-height: 120px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
}

.upload-preview.show {
    display: block;
}

.file-name {
    font-size: 0.75rem;
    color: #22c55e;
    margin-top: 0.5rem;
    word-break: break-all;
}

/* Guidelines Card */
.guidelines-card {
    background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
    border: 1px solid #fde047;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 2rem;
}

.guidelines-card h5 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #854d0e;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.guidelines-card ul {
    margin: 0;
    padding-left: 1.25rem;
}

.guidelines-card li {
    font-size: 0.875rem;
    color: #a16207;
    margin-bottom: 0.375rem;
}

.guidelines-card li:last-child {
    margin-bottom: 0;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

.submit-btn:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Back Link */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.9375rem;
    margin-top: 1.5rem;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: #6366f1;
}

/* Privacy Notice */
.privacy-notice {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-top: 1.5rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.privacy-notice i {
    color: #22c55e;
    font-size: 1.125rem;
    margin-top: 0.125rem;
}

.privacy-notice p {
    font-size: 0.8125rem;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}

/* Error styling */
.is-invalid {
    border-color: #ef4444 !important;
}

.invalid-feedback {
    color: #ef4444;
    font-size: 0.8125rem;
    margin-top: 0.5rem;
}

/* Alert styling */
.alert-modern {
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-modern.alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.alert-modern.alert-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
}

/* Responsive */
@media (max-width: 640px) {
    .manual-kyc-container {
        padding: 1rem;
    }

    .kyc-card-body {
        padding: 1.5rem;
    }

    .upload-cards {
        grid-template-columns: 1fr;
    }

    .kyc-header h1 {
        font-size: 1.5rem;
    }
}
</style>

<div class="manual-kyc-container">
    <!-- Header -->
    <div class="kyc-header">
        <div class="kyc-header-icon">
            <i class="fas fa-id-card"></i>
        </div>
        <h1>Manual ID Verification</h1>
        <p>Submit your Philippine government-issued ID for manual verification by our team</p>
    </div>

    <!-- Main Card -->
    <div class="kyc-card">
        <div class="kyc-card-body">
            @if(session('error'))
                <div class="alert-modern alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert-modern alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($pendingSubmission)
                <!-- Pending Submission Banner -->
                <div class="pending-banner">
                    <h4><i class="fas fa-clock"></i> Verification Pending</h4>
                    <p>You have submitted <strong>{{ $pendingSubmission->document_type }}</strong> on {{ $pendingSubmission->created_at->format('F d, Y') }}. Our team is reviewing your documents.</p>
                    <div class="btn-group">
                        <a href="{{ route('kyc.manual.status') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-eye me-1"></i>View Status
                        </a>
                        <form action="{{ route('kyc.manual.cancel', $pendingSubmission) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this submission?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-times me-1"></i>Cancel & Resubmit
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Info Banner -->
                <div class="info-banner">
                    <div class="info-banner-icon">
                        <i class="fas fa-info"></i>
                    </div>
                    <div class="info-banner-content">
                        <h4>Manual Review Process</h4>
                        <p>Your documents will be reviewed by our team within 1-3 business days. You'll be notified once verification is complete.</p>
                    </div>
                </div>

                <!-- Upload Form -->
                <form action="{{ route('kyc.manual.upload') }}" method="POST" enctype="multipart/form-data" id="manualKycForm">
                    @csrf

                    <!-- Step 1: Select ID Type -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <span class="step-number">1</span>
                            Select Your ID Type
                        </div>
                        <div class="custom-select-wrapper">
                            <select name="document_type" id="document_type" class="@error('document_type') is-invalid @enderror" required>
                                <option value="">-- Choose your ID type --</option>
                                @foreach($idTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('document_type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('document_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Step 2: Enter ID Number -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <span class="step-number">2</span>
                            Enter Your ID Number
                        </div>
                        <input type="text"
                               name="document_number"
                               id="document_number"
                               class="custom-input @error('document_number') is-invalid @enderror"
                               value="{{ old('document_number') }}"
                               placeholder="Enter the ID number as shown on your document"
                               required>
                        @error('document_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Step 3: Upload Documents -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <span class="step-number">3</span>
                            Upload Your Documents
                        </div>

                        <div class="upload-cards">
                            <!-- Front of ID -->
                            <div class="upload-card" id="frontCard">
                                <span class="required-badge">Required</span>
                                <input type="file"
                                       name="document_front"
                                       id="document_front"
                                       accept="image/jpeg,image/jpg,image/png"
                                       required>
                                <div class="upload-card-icon">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div class="upload-card-title">Front of ID</div>
                                <div class="upload-card-subtitle">JPG or PNG, max 5MB</div>
                                <div class="upload-preview" id="frontPreview">
                                    <img src="" alt="Front Preview">
                                </div>
                                <div class="file-name" id="frontFileName"></div>
                            </div>

                            <!-- Back of ID -->
                            <div class="upload-card" id="backCard">
                                <span class="optional-badge">Optional</span>
                                <input type="file"
                                       name="document_back"
                                       id="document_back"
                                       accept="image/jpeg,image/jpg,image/png">
                                <div class="upload-card-icon">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div class="upload-card-title">Back of ID</div>
                                <div class="upload-card-subtitle">If applicable</div>
                                <div class="upload-preview" id="backPreview">
                                    <img src="" alt="Back Preview">
                                </div>
                                <div class="file-name" id="backFileName"></div>
                            </div>

                            <!-- Selfie -->
                            <div class="upload-card" id="selfieCard">
                                <span class="required-badge">Required</span>
                                <input type="file"
                                       name="selfie"
                                       id="selfie"
                                       accept="image/jpeg,image/jpg,image/png"
                                       required>
                                <div class="upload-card-icon">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <div class="upload-card-title">Selfie with ID</div>
                                <div class="upload-card-subtitle">Hold ID next to face</div>
                                <div class="upload-preview" id="selfiePreview">
                                    <img src="" alt="Selfie Preview">
                                </div>
                                <div class="file-name" id="selfieFileName"></div>
                            </div>
                        </div>

                        @error('document_front')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('selfie')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Guidelines -->
                    <div class="guidelines-card">
                        <h5><i class="fas fa-lightbulb"></i> Photo Tips</h5>
                        <ul>
                            <li>Make sure all text on the ID is clearly readable</li>
                            <li>Avoid glare, shadows, or blurry images</li>
                            <li>For selfie: Your face and ID should both be clearly visible</li>
                            <li>Ensure your ID is not expired</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        Submit for Verification
                    </button>
                </form>
            @endif

            <!-- Privacy Notice -->
            <div class="privacy-notice">
                <i class="fas fa-shield-alt"></i>
                <p>Your documents are securely encrypted and stored. We only use them for identity verification purposes and never share them with third parties.</p>
            </div>

            <!-- Back Link -->
            <div class="text-center">
                <a href="{{ route('kyc.start.form') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Verification Options
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    function setupUpload(inputId, cardId, previewId, fileNameId) {
        const input = document.getElementById(inputId);
        const card = document.getElementById(cardId);
        const preview = document.getElementById(previewId);
        const fileName = document.getElementById(fileNameId);

        if (input && card) {
            input.addEventListener('change', function() {
                const file = this.files[0];

                if (file) {
                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must not exceed 5MB');
                        this.value = '';
                        card.classList.remove('has-file');
                        preview.classList.remove('show');
                        fileName.textContent = '';
                        return;
                    }

                    // Validate file type
                    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                        alert('Only JPG and PNG images are allowed');
                        this.value = '';
                        card.classList.remove('has-file');
                        preview.classList.remove('show');
                        fileName.textContent = '';
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.querySelector('img').src = e.target.result;
                        preview.classList.add('show');
                    };
                    reader.readAsDataURL(file);

                    // Update card state
                    card.classList.add('has-file');
                    fileName.textContent = file.name;
                } else {
                    card.classList.remove('has-file');
                    preview.classList.remove('show');
                    fileName.textContent = '';
                }
            });
        }
    }

    setupUpload('document_front', 'frontCard', 'frontPreview', 'frontFileName');
    setupUpload('document_back', 'backCard', 'backPreview', 'backFileName');
    setupUpload('selfie', 'selfieCard', 'selfiePreview', 'selfieFileName');

    // Form submission
    const form = document.getElementById('manualKycForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Uploading...';
        });
    }
});
</script>
@endpush
@endsection
