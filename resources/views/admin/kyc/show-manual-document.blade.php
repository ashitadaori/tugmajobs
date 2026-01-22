@extends('layouts.admin')

@section('page_title', 'Review KYC Document')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.kyc.manual-documents') }}">Manual KYC Documents</a></li>
            <li class="breadcrumb-item active">Review Document</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <i class="fas fa-file-alt me-2"></i>
            Review KYC Document
        </h1>
        <a href="{{ route('admin.kyc.manual-documents') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
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
        <!-- Left Column - User & Document Info -->
        <div class="col-lg-4">
            <!-- User Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($document->user->profile_image)
                            <img src="{{ $document->user->profile_image }}"
                                 class="rounded-circle mb-3"
                                 width="100" height="100"
                                 alt="{{ $document->user->name }}">
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                 style="width: 100px; height: 100px; font-size: 40px;">
                                {{ strtoupper(substr($document->user->name, 0, 1)) }}
                            </div>
                        @endif
                        <h5 class="mb-1">{{ $document->user->name }}</h5>
                        <p class="text-muted mb-2">{{ $document->user->email }}</p>
                        <span class="badge bg-{{ $document->user->role === 'employer' ? 'info' : 'primary' }}">
                            {{ ucfirst($document->user->role ?? 'User') }}
                        </span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <strong>Account Created:</strong>
                        <div class="text-muted">{{ $document->user->created_at->format('M j, Y g:i A') }}</div>
                    </div>

                    <div class="mb-3">
                        <strong>Current KYC Status:</strong>
                        @php
                            $userKycStatus = $document->user->kyc_status ?? 'pending';
                            $userKycColor = match($userKycStatus) {
                                'verified' => 'success',
                                'failed' => 'danger',
                                'in_progress' => 'info',
                                default => 'warning'
                            };
                        @endphp
                        <span class="badge bg-{{ $userKycColor }}">{{ ucfirst($userKycStatus) }}</span>
                    </div>

                    @if($document->user->kyc_verified_at)
                    <div class="mb-3">
                        <strong>Verified At:</strong>
                        <div class="text-muted">{{ $document->user->kyc_verified_at->format('M j, Y g:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Document Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card me-2"></i>Document Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Document Type:</strong>
                        <div>{{ $document->document_type }}</div>
                    </div>

                    @if($document->document_number)
                    <div class="mb-3">
                        <strong>Document Number:</strong>
                        <div><code>{{ $document->document_number }}</code></div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong>Submission Status:</strong>
                        @php
                            $statusColor = match($document->status) {
                                'verified' => 'success',
                                'rejected' => 'danger',
                                default => 'warning'
                            };
                        @endphp
                        <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($document->status) }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Submitted On:</strong>
                        <div>{{ $document->created_at->format('M j, Y g:i A') }}</div>
                        <small class="text-muted">{{ $document->created_at->diffForHumans() }}</small>
                    </div>

                    @if($document->updated_at && $document->updated_at != $document->created_at)
                    <div class="mb-3">
                        <strong>Last Updated:</strong>
                        <div>{{ $document->updated_at->format('M j, Y g:i A') }}</div>
                    </div>
                    @endif

                    @if($document->status === 'rejected' && $document->rejection_reason)
                    <div class="alert alert-danger mb-0">
                        <strong><i class="fas fa-times-circle me-1"></i>Rejection Reason:</strong>
                        <p class="mb-0 mt-2">{{ $document->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            @if($document->status === 'pending')
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-gavel me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.kyc.verify-manual-document', $document) }}"
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to APPROVE this KYC document? This will verify the user\'s identity.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check-circle me-2"></i>Approve Document
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-danger btn-lg"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal">
                            <i class="fas fa-times-circle me-2"></i>Reject Document
                        </button>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Review Guidelines:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            <li>Verify the document is clear and readable</li>
                            <li>Check that document details match user information</li>
                            <li>Ensure the selfie matches the ID photo</li>
                            <li>Look for signs of tampering or manipulation</li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Document Images -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2"></i>Submitted Documents
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($files) > 0)
                        <div class="row g-4">
                            @if(!empty($files['front']))
                            <div class="col-md-6">
                                <div class="document-preview">
                                    <h6 class="mb-3">
                                        <i class="fas fa-id-card text-primary me-2"></i>Front of ID
                                    </h6>
                                    <div class="document-image-container">
                                        <img src="{{ route('kyc.manual.view', [$document, 'front']) }}"
                                             alt="ID Front"
                                             class="img-fluid rounded shadow document-image"
                                             onclick="openImageModal(this.src, 'Front of ID')">
                                        <div class="image-overlay">
                                            <button type="button" class="btn btn-light btn-sm" onclick="openImageModal('{{ route('kyc.manual.view', [$document, 'front']) }}', 'Front of ID')">
                                                <i class="fas fa-expand"></i> View Full Size
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(!empty($files['back']))
                            <div class="col-md-6">
                                <div class="document-preview">
                                    <h6 class="mb-3">
                                        <i class="fas fa-id-card text-info me-2"></i>Back of ID
                                    </h6>
                                    <div class="document-image-container">
                                        <img src="{{ route('kyc.manual.view', [$document, 'back']) }}"
                                             alt="ID Back"
                                             class="img-fluid rounded shadow document-image"
                                             onclick="openImageModal(this.src, 'Back of ID')">
                                        <div class="image-overlay">
                                            <button type="button" class="btn btn-light btn-sm" onclick="openImageModal('{{ route('kyc.manual.view', [$document, 'back']) }}', 'Back of ID')">
                                                <i class="fas fa-expand"></i> View Full Size
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(!empty($files['selfie']))
                            <div class="col-md-6">
                                <div class="document-preview">
                                    <h6 class="mb-3">
                                        <i class="fas fa-camera text-warning me-2"></i>Selfie / Portrait
                                    </h6>
                                    <div class="document-image-container">
                                        <img src="{{ route('kyc.manual.view', [$document, 'selfie']) }}"
                                             alt="Selfie"
                                             class="img-fluid rounded shadow document-image"
                                             onclick="openImageModal(this.src, 'Selfie / Portrait')">
                                        <div class="image-overlay">
                                            <button type="button" class="btn btn-light btn-sm" onclick="openImageModal('{{ route('kyc.manual.view', [$document, 'selfie']) }}', 'Selfie / Portrait')">
                                                <i class="fas fa-expand"></i> View Full Size
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Handle any other file types --}}
                            @foreach($files as $type => $path)
                                @if(!in_array($type, ['front', 'back', 'selfie']) && $path)
                                <div class="col-md-6">
                                    <div class="document-preview">
                                        <h6 class="mb-3">
                                            <i class="fas fa-file text-secondary me-2"></i>{{ ucfirst($type) }}
                                        </h6>
                                        <div class="document-image-container">
                                            <img src="{{ route('kyc.manual.view', [$document, $type]) }}"
                                                 alt="{{ ucfirst($type) }}"
                                                 class="img-fluid rounded shadow document-image"
                                                 onclick="openImageModal(this.src, '{{ ucfirst($type) }}')">
                                            <div class="image-overlay">
                                                <button type="button" class="btn btn-light btn-sm" onclick="openImageModal('{{ route('kyc.manual.view', [$document, $type]) }}', '{{ ucfirst($type) }}')">
                                                    <i class="fas fa-expand"></i> View Full Size
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <a href="{{ route('admin.kyc.download-manual-document', $document) }}" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i>Download Documents
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-image fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No document images available.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Verification Checklist -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>Verification Checklist
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check1">
                                <label class="form-check-label" for="check1">
                                    Document is clear and readable
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check2">
                                <label class="form-check-label" for="check2">
                                    Document appears to be authentic
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check3">
                                <label class="form-check-label" for="check3">
                                    Document is not expired
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check4">
                                <label class="form-check-label" for="check4">
                                    Photo matches the selfie
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check5">
                                <label class="form-check-label" for="check5">
                                    Name matches user account
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check6">
                                <label class="form-check-label" for="check6">
                                    No signs of tampering
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle text-danger me-2"></i>
                    Reject KYC Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.kyc.reject-manual-document', $document) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p><strong>User:</strong> {{ $document->user->name }} ({{ $document->user->email }})</p>
                    <p><strong>Document:</strong> {{ $document->document_type }}</p>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                                  placeholder="Please provide a clear reason for rejection..."></textarea>
                        <div class="form-text">This reason will be shared with the user so they can resubmit with correct documents.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quick Rejection Reasons:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-reason" data-reason="Document is blurry or unreadable. Please resubmit a clear photo.">Blurry</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-reason" data-reason="Document appears to be expired. Please submit a valid, non-expired ID.">Expired</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-reason" data-reason="Selfie does not match the photo on the ID. Please ensure your face is clearly visible.">Photo Mismatch</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-reason" data-reason="Document type is not accepted. Please submit one of the accepted ID types.">Wrong Type</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-reason" data-reason="Parts of the document are cut off. Please capture the full document.">Incomplete</button>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Rejecting this document will set the user's KYC status to "failed" and they will need to resubmit their documents.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Reject Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Document Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="modalImage" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.document-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
}

.document-image {
    cursor: pointer;
    transition: transform 0.3s ease;
    width: 100%;
    height: auto;
    min-height: 200px;
    object-fit: cover;
}

.document-image:hover {
    transform: scale(1.02);
}

.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    padding: 1rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    text-align: center;
}

.document-image-container:hover .image-overlay {
    opacity: 1;
}

.document-preview {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    height: 100%;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.quick-reason {
    font-size: 0.75rem;
}

.quick-reason:hover {
    background-color: #6c757d;
    color: white;
}
</style>
@endsection

@section('scripts')
<script>
function openImageModal(src, title) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModalTitle').textContent = title;
    var modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Quick rejection reason buttons
    document.querySelectorAll('.quick-reason').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var textarea = document.querySelector('textarea[name="rejection_reason"]');
            textarea.value = this.dataset.reason;
            textarea.focus();
        });
    });
});
</script>
@endsection
