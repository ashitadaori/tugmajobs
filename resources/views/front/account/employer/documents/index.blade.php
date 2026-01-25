@extends('layouts.employer')

@section('title', 'Document Verification')
@section('page_title', 'Employer Documents')

@section('content')
<!-- CACHE BUSTER: {{ now() }} -->
<div class="row">
    <!-- Verification Status -->
    <div class="col-12 mb-4">
        <div class="ep-card">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-shield-check"></i>
                    Verification Status
                </h3>
                <div class="ep-card-actions">
                    @if(auth()->user()->isKycVerified())
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i> Verified
                        </span>
                    @else
                        <button type="button" class="ep-btn ep-btn-sm ep-btn-primary" data-bs-toggle="modal" data-bs-target="#kycOptionsModal">
                            <i class="bi bi-shield-lock me-1"></i> Complete KYC
                        </button>
                    @endif
                </div>
            </div>
            <div class="ep-card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-light p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi {{ auth()->user()->canPostJobs() ? 'bi-check-lg text-success' : 'bi-pause-circle text-warning' }} fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-semibold">Job Posting Access</h5>
                                <p class="mb-0 text-muted small">
                                    @if(auth()->user()->canPostJobs())
                                        Your account is verified. You can post job openings freely.
                                    @else
                                        Upload required documents to unlock job posting features.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="col-12">
        <div class="ep-card">
            <div class="ep-card-header">
                <h3 class="ep-card-title">
                    <i class="bi bi-file-earmark-text"></i>
                    Uploaded Documents
                </h3>
                <div class="ep-card-actions">
                    <a href="{{ route('employer.documents.create') }}" class="ep-btn ep-btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        Upload Document
                    </a>
                </div>
            </div>
            <div class="ep-card-body p-0">
                @if($documents->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">
                            <i class="bi bi-folder2-open display-1"></i>
                        </div>
                        <h5 class="fw-semibold text-secondary">No documents found</h5>
                        <p class="text-muted small mb-4">Upload your business documents to get verified.</p>
                        <a href="{{ route('employer.documents.create') }}" class="ep-btn ep-btn-outline">
                            <i class="bi bi-upload me-2"></i> Upload First Document
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted small text-uppercase fw-semibold">Document Type</th>
                                    <th class="py-3 text-muted small text-uppercase fw-semibold">File Name</th>
                                    <th class="py-3 text-muted small text-uppercase fw-semibold">Status</th>
                                    <th class="py-3 text-muted small text-uppercase fw-semibold">Date</th>
                                    <th class="pe-4 py-3 text-end text-muted small text-uppercase fw-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $document)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded bg-light p-2 text-primary">
                                                    <i class="bi bi-file-text fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium text-dark">
                                                        {{ $document->document_type_config['label'] ?? ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                                    </div>
                                                    @if($document->document_type_config['required'] ?? false)
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small" style="font-size: 0.65rem;">Required</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark fw-medium">{{ $document->document_name }}</div>
                                            <small class="text-muted">{{ $document->formatted_file_size }}</small>
                                        </td>
                                        <td>
                                            @if($document->isApproved())
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                    <i class="bi bi-check-circle me-1"></i> Approved
                                                </span>
                                            @elseif($document->isPending())
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                                    <i class="bi bi-hourglass-split me-1"></i> Under Review
                                                </span>
                                            @elseif($document->isRejected())
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">
                                                    {{ $document->status_label }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $document->submitted_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $document->submitted_at->format('g:i A') }}</small>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('employer.documents.show', $document) }}" 
                                                   class="btn btn-sm btn-light border" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('employer.documents.download', $document) }}" 
                                                   class="btn btn-sm btn-light border" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @if($document->isRejected())
                                                    <a href="{{ route('employer.documents.edit', $document) }}" 
                                                       class="btn btn-sm btn-warning-subtle text-warning border border-warning-subtle" title="Resubmit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif
                                                @if(!$document->isApproved())
                                                    <button type="button" class="btn btn-sm btn-danger-subtle text-danger border border-danger-subtle" 
                                                            onclick="deleteDocument({{ $document->id }})" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if($document->isRejected() && $document->admin_notes)
                                        <tr class="bg-danger-subtle">
                                            <td colspan="5" class="ps-4 pe-4 py-2">
                                                <div class="d-flex align-items-center text-danger small">
                                                    <i class="bi bi-exclamation-circle me-2"></i>
                                                    <strong>Admin Notes:</strong>&nbsp;{{ $document->admin_notes }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Delete Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3 text-danger opacity-75">
                    <i class="bi bi-trash3 display-1"></i>
                </div>
                <h5 class="fw-semibold mb-2">Are you sure?</h5>
                <p class="text-muted mb-0">This action cannot be undone. The document will be permanently removed.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                <button type="button" class="ep-btn ep-btn-sm ep-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ep-btn ep-btn-sm ep-btn-danger">
                        <i class="bi bi-trash me-2"></i> Delete Document
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- KYC Options Modal -->
<div class="modal fade" id="kycOptionsModal" tabindex="-1" aria-labelledby="kycOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 py-3" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                <h5 class="modal-title text-white fw-semibold" id="kycOptionsModalLabel">
                    <i class="bi bi-shield-check me-2"></i>Choose Verification Method
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted text-center mb-4">Select your preferred method to verify your identity</p>

                <div class="row g-4">
                    <!-- Instant Verification (Didit) -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 kyc-option-card" style="border-color: #6366f1; border-radius: 12px; cursor: pointer;" onclick="selectKycOption('didit')">
                            <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 10px 10px 0 0;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lightning-charge-fill me-2 fs-5"></i>
                                    <span class="fw-semibold">Instant Verification</span>
                                    <span class="badge bg-white text-primary ms-auto">Recommended</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Automated verification using Didit - get results in minutes!</p>
                                <h6 class="fw-semibold small mb-2">Supported IDs:</h6>
                                <ul class="small text-muted ps-3 mb-3">
                                    <li>Passport</li>
                                    <li>Driver's License</li>
                                    <li>National ID / Identity Card</li>
                                    <li>Residence Permit</li>
                                </ul>
                                <div class="d-flex align-items-center text-muted small" style="background: #f1f5f9; padding: 8px 12px; border-radius: 8px;">
                                    <i class="bi bi-clock me-2"></i>
                                    <span>Takes only 2-5 minutes</span>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-0 p-3">
                                <button type="button" class="btn w-100 text-white fw-semibold" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 8px;" onclick="event.stopPropagation(); startDigitalKyc();">
                                    <i class="bi bi-play-fill me-1"></i> Start Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Verification -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 kyc-option-card" style="border-color: #64748b; border-radius: 12px; cursor: pointer;" onclick="selectKycOption('manual')">
                            <div class="card-header text-white py-3" style="background: #64748b; border-radius: 10px 10px 0 0;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-upload me-2 fs-5"></i>
                                    <span class="fw-semibold">Manual Verification</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">For Philippine IDs not supported by automated verification.</p>
                                <h6 class="fw-semibold small mb-2">Supported IDs:</h6>
                                <ul class="small text-muted ps-3 mb-3">
                                    <li>PhilHealth ID / UMID</li>
                                    <li>SSS ID / Postal ID</li>
                                    <li>Voter's ID / PRC ID</li>
                                    <li>And many more...</li>
                                </ul>
                                <div class="d-flex align-items-center text-muted small" style="background: #f1f5f9; padding: 8px 12px; border-radius: 8px;">
                                    <i class="bi bi-person-badge me-2"></i>
                                    <span>Review takes 1-3 business days</span>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-0 p-3">
                                <a href="{{ route('kyc.manual.form') }}" class="btn w-100 text-white fw-semibold" style="background: #64748b; border-radius: 8px;" onclick="event.stopPropagation();">
                                    <i class="bi bi-upload me-1"></i> Upload Documents
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="mt-4 p-3" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;">
                    <h6 class="fw-semibold small mb-2" style="color: #92400e;">
                        <i class="bi bi-lightbulb me-1"></i> Important Tips
                    </h6>
                    <ul class="small mb-0 ps-3" style="color: #a16207;">
                        <li>Make sure your documents are not expired</li>
                        <li>Ensure photos are clear and readable</li>
                        <li>Complete the process in one session</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.kyc-option-card {
    transition: all 0.2s ease;
}
.kyc-option-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
</style>
@endsection

@push('scripts')
<!-- KYC Inline Verification Script -->
<script>
    // Set current user ID for KYC polling
    window.currentUserId = {{ auth()->id() }};
</script>
<script src="{{ asset('assets/js/kyc-inline-verification.js') }}"></script>

<script>
function deleteDocument(documentId) {
    const form = document.getElementById('deleteForm');
    form.action = `/employer/documents/${documentId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function selectKycOption(option) {
    if (option === 'didit') {
        startDigitalKyc();
    } else if (option === 'manual') {
        window.location.href = '{{ route("kyc.manual.form") }}';
    }
}

function startDigitalKyc() {
    // Close the options modal
    const optionsModal = bootstrap.Modal.getInstance(document.getElementById('kycOptionsModal'));
    if (optionsModal) {
        optionsModal.hide();
    }

    // Wait for modal to close, then start Didit verification
    setTimeout(() => {
        if (typeof startInlineVerification === 'function') {
            // Create a fake event for the function
            const fakeEvent = {
                target: document.createElement('button'),
                preventDefault: function() {}
            };
            fakeEvent.target.innerHTML = '<i class="bi bi-shield-lock me-1"></i> Complete KYC';
            fakeEvent.target.disabled = false;
            startInlineVerification(fakeEvent);
        } else {
            console.error('startInlineVerification function not available');
            alert('Unable to start verification. Please refresh the page and try again.');
        }
    }, 300);
}
</script>
@endpush
