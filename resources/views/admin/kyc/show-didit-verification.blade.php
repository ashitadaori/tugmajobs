@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">
                <i class="fas fa-id-card me-2"></i>
                KYC Verification Details
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.kyc.didit-verifications') }}">KYC Verifications</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.kyc.didit-verifications') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($user->profile_image)
                            <img src="{{ $user->profile_image }}" 
                                 class="rounded-circle mb-3" 
                                 width="80" height="80" 
                                 alt="{{ $user->name }}">
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 80px; height: 80px; font-size: 32px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <h5>{{ $user->name }}</h5>
                        <span class="badge bg-{{ $user->role === 'employer' ? 'info' : 'primary' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Email:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $user->email }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Joined:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $user->created_at->format('M j, Y') }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>KYC Status:</strong>
                        </div>
                        <div class="col-sm-6">
    @php
                                // Status color mapping for badges
                                $statusColor = match($status) {
                                    'verified' => 'success',
                                    'failed' => 'danger',
                                    'expired' => 'warning',
                                    'in_progress' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($status !== 'verified')
                        <form action="{{ route('admin.kyc.approve-didit-verification', $user) }}" 
                              method="POST" class="mb-2"
                              onsubmit="return confirm('Are you sure you want to approve this KYC verification?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-1"></i>Approve Verification
                            </button>
                        </form>
                    @endif
                    
                    @if(!in_array($status, ['failed', 'verified']))
                        <button type="button"
                                class="btn btn-danger w-100 mb-2"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal">
                            <i class="fas fa-times me-1"></i>Reject Verification
                        </button>
                    @endif

                    <!-- Reset KYC Button - Always available -->
                    <button type="button"
                            class="btn btn-warning w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#resetKycModal">
                        <i class="fas fa-redo me-1"></i>Reset KYC
                    </button>
                </div>
            </div>
        </div>

        <!-- Verification Details -->
        <div class="col-lg-8">
            <!-- Verification Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Verification Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Session ID:</th>
                                    <td>
                                        @if($sessionId)
                                            <code>{{ $sessionId }}</code>
                                        @else
                                            <span class="text-muted">Not available</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($verification && $verification->created_at)
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $verification->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                                @endif
                                @if($verification && $verification->verified_at)
                                <tr>
                                    <th>Verified:</th>
                                    <td>{{ $verification->verified_at->format('M j, Y g:i A') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @php
                                    // Get document info from either source
                                    $documentType = ($kycData && $kycData->document_type) ? $kycData->document_type : ($verification && $verification->document_type ? $verification->document_type : null);
                                    $documentNumber = ($kycData && $kycData->document_number) ? $kycData->document_number : ($verification && $verification->document_number ? $verification->document_number : null);
                                    $nationality = ($kycData && $kycData->nationality) ? $kycData->nationality : ($verification && $verification->nationality ? $verification->nationality : null);
                                @endphp
                                
                                @if($documentType)
                                <tr>
                                    <th width="40%">Document Type:</th>
                                    <td>{{ $documentType }}</td>
                                </tr>
                                @endif
                                @if($documentNumber)
                                <tr>
                                    <th>Document Number:</th>
                                    <td>
                                        <code>{{ substr($documentNumber, 0, -4) }}****</code>
                                    </td>
                                </tr>
                                @endif
                                @if($nationality)
                                <tr>
                                    <th>Nationality:</th>
                                    <td>{{ $nationality }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            @php
                // Get personal info from either source
                $fullName = null;
                $dateOfBirth = null;
                $gender = null;
                $address = null;
                
                // Priority 1: KycData
                if ($kycData) {
                    $fullName = $kycData->full_name ?? trim(($kycData->first_name ?? '') . ' ' . ($kycData->last_name ?? ''));
                    $dateOfBirth = $kycData->date_of_birth;
                    $gender = $kycData->gender;
                    $address = $kycData->address ?? $kycData->formatted_address;
                }
                
                // Priority 2: KycVerification (fallback)
                if (!$fullName && $verification) {
                    $fullName = trim(($verification->firstname ?? '') . ' ' . ($verification->lastname ?? ''));
                }
                if (!$dateOfBirth && $verification) {
                    $dateOfBirth = $verification->date_of_birth;
                }
                if (!$gender && $verification) {
                    $gender = $verification->gender;
                }
                if (!$address && $verification) {
                    $address = $verification->address;
                }
                
                $hasPersonalInfo = $fullName || $dateOfBirth || $gender || $address;
            @endphp
            
            @if($hasPersonalInfo)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($fullName)
                            <div class="mb-3">
                                <strong>Full Name:</strong><br>
                                {{ $fullName }}
                            </div>
                            @endif
                            
                            @if($dateOfBirth)
                            <div class="mb-3">
                                <strong>Date of Birth:</strong><br>
                                {{ \Carbon\Carbon::parse($dateOfBirth)->format('F j, Y') }}
                            </div>
                            @endif
                            
                            @if($gender)
                            <div class="mb-3">
                                <strong>Gender:</strong><br>
                                {{ $gender }}
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($address)
                            <div class="mb-3">
                                <strong>Address:</strong><br>
                                {{ $address }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Verification Images -->
            @if(!empty($documentImages))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-camera me-2"></i>Verification Images
                        <small class="text-muted ms-2">({{ count($documentImages) }} photos)</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if(isset($documentImages['front']))
                        <div class="col-lg-4 col-md-6">
                            <div class="verification-image-card p-3 border rounded bg-light">
                                <div class="image-header text-center mb-2">
                                    <h6 class="mb-1 text-primary">
                                        <i class="fas fa-id-card me-1"></i>
                                        Document Front
                                    </h6>
                                    <small class="text-muted">Front side of ID document</small>
                                </div>
                                <div class="image-container">
                                    <img src="{{ $documentImages['front'] }}" 
                                         class="img-fluid rounded border shadow-sm" 
                                         alt="Document Front Image"
                                         style="cursor: pointer; max-height: 250px; width: 100%; object-fit: cover;"
                                         onclick="showImageModal('{{ $documentImages['front'] }}', 'Document Front - ID Verification')">
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($documentImages['back']))
                        <div class="col-lg-4 col-md-6">
                            <div class="verification-image-card p-3 border rounded bg-light">
                                <div class="image-header text-center mb-2">
                                    <h6 class="mb-1 text-success">
                                        <i class="fas fa-id-card-alt me-1"></i>
                                        Document Back
                                    </h6>
                                    <small class="text-muted">Back side of ID document</small>
                                </div>
                                <div class="image-container">
                                    <img src="{{ $documentImages['back'] }}" 
                                         class="img-fluid rounded border shadow-sm" 
                                         alt="Document Back Image"
                                         style="cursor: pointer; max-height: 250px; width: 100%; object-fit: cover;"
                                         onclick="showImageModal('{{ $documentImages['back'] }}', 'Document Back - ID Verification')">
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($documentImages['portrait']))
                        <div class="col-lg-4 col-md-6">
                            <div class="verification-image-card p-3 border rounded bg-light">
                                <div class="image-header text-center mb-2">
                                    <h6 class="mb-1 text-info">
                                        <i class="fas fa-user-circle me-1"></i>
                                        Live Selfie
                                    </h6>
                                    <small class="text-muted">Selfie taken during verification</small>
                                </div>
                                <div class="image-container">
                                    <img src="{{ $documentImages['portrait'] }}" 
                                         class="img-fluid rounded border shadow-sm" 
                                         alt="Selfie Verification Image"
                                         style="cursor: pointer; max-height: 250px; width: 100%; object-fit: cover;"
                                         onclick="showImageModal('{{ $documentImages['portrait'] }}', 'Live Selfie - Identity Verification')">
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @foreach($documentImages as $type => $imageUrl)
                            @if(!in_array($type, ['front', 'back', 'portrait']))
                            <div class="col-lg-4 col-md-6">
                                <div class="verification-image-card p-3 border rounded bg-light">
                                    <div class="image-header text-center mb-2">
                                        <h6 class="mb-1 text-secondary">
                                            <i class="fas fa-image me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </h6>
                                        <small class="text-muted">Additional verification image</small>
                                    </div>
                                    <div class="image-container">
                                        <img src="{{ $imageUrl }}" 
                                             class="img-fluid rounded border shadow-sm" 
                                             alt="{{ ucfirst($type) }} Image"
                                             style="cursor: pointer; max-height: 250px; width: 100%; object-fit: cover;"
                                             onclick="showImageModal('{{ $imageUrl }}', '{{ ucfirst(str_replace('_', ' ', $type)) }}')">
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Click on any image to view full size • These are the actual photos taken during verification
                        </small>
                    </div>
                </div>
            </div>
            @else
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-camera text-muted me-2"></i>Verification Images
                    </h5>
                </div>
                <div class="card-body text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-images fa-3x mb-3 opacity-25"></i>
                        <p class="mb-1">No verification images available</p>
                        <small>The original photos taken during KYC verification are not accessible</small>
                    </div>
                </div>
            </div>
            @endif

            <!-- Raw Verification Data -->
            @if($verification && ($verification->raw_data || $verification->verification_data))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>Raw Verification Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="rawDataAccordion">
                        @if($verification->verification_data)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="verificationDataHeading">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#verificationDataCollapse">
                                    Verification Data
                                </button>
                            </h2>
                            <div id="verificationDataCollapse" class="accordion-collapse collapse" 
                                 data-bs-parent="#rawDataAccordion">
                                <div class="accordion-body">
                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($verification->verification_data, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($verification->raw_data)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="rawDataHeading">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#rawDataCollapse">
                                    Raw Data
                                </button>
                            </h2>
                            <div id="rawDataCollapse" class="accordion-collapse collapse" 
                                 data-bs-parent="#rawDataAccordion">
                                <div class="accordion-body">
                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($verification->raw_data, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Document Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid rounded" alt="Document Image">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadImageBtn" href="" download class="btn btn-primary">
                    <i class="fas fa-download me-1"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
@if(!in_array($status, ['failed', 'verified']))
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject KYC Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.kyc.reject-didit-verification', $user) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action will reject the KYC verification for <strong>{{ $user->name }}</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                                  placeholder="Please provide a detailed reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Reset KYC Modal -->
<div class="modal fade" id="resetKycModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-redo text-warning me-2"></i>Reset KYC Verification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.kyc.reset', $user) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will:
                    </div>
                    <ul class="mb-3">
                        <li>Delete all KYC verification records for <strong>{{ $user->name }}</strong></li>
                        <li>Delete all KYC data including document images</li>
                        <li>Reset their KYC status to <span class="badge bg-secondary">Pending</span></li>
                        <li>Allow the user to start a new KYC verification process</li>
                    </ul>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This is useful when the user needs to re-verify their identity (e.g., expired documents, incorrect information, or technical issues during verification).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-redo me-1"></i>Reset KYC
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showImageModal(imageUrl, title) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('imageModalTitle').textContent = title;
    document.getElementById('downloadImageBtn').href = imageUrl;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>
@endsection

@section('styles')
<style>
.document-image-container img {
    max-height: 300px;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.document-image-container img:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.table-borderless th {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    font-weight: 600;
}

.table-borderless td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    font-size: 0.8em;
}

pre code {
    font-size: 0.85em;
    max-height: 400px;
    overflow-y: auto;
}

.user-avatar {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endsection
