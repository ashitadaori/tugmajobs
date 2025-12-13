@extends('layouts.employer')

@section('title', 'Document Verification')
@section('page_title', 'Employer Documents')

@section('content')
<!-- CACHE BUSTER: {{ now() }} -->
<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">
            <!-- VISIBLE PAGE TITLE -->
            <div class="mb-4 p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h2 class="text-white mb-0 fw-bold">
                    <i class="bi bi-file-earmark-text me-3"></i>Employer Documents
                </h2>
                <p class="text-white mb-0 mt-2 opacity-75">Manage your verification documents</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Document Verification</h4>
                    <p class="text-muted mb-0">Upload required documents to complete your employer verification</p>
                </div>
                <a href="{{ route('employer.documents.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Upload Document
                </a>
            </div>

            <!-- Verification Progress -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="verification-progress-card documents-card">
                        <div class="card-header">
                            <h6>Verification Progress</h6>
                        </div>
                        <div class="card-body">
                            
                            <!-- KYC Status -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    @if(auth()->user()->isKycVerified())
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    @else
                                        <i class="fas fa-clock text-warning fa-lg"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium">KYC Verification</span>
                                        @if(auth()->user()->isKycVerified())
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="startInlineVerification(event)">
                                                <i class="fas fa-shield-alt me-1"></i>Complete KYC
                                            </button>
                                        @endif
                                    </div>
                                    <small class="text-muted">Identity verification using official documents</small>
                                </div>
                            </div>

                            <hr>

                            <!-- Documents Status -->
                            @php
                                $requiredTypes = collect(\App\Models\EmployerDocument::getDocumentTypes())
                                    ->filter(fn($config) => $config['required']);
                                $completedRequired = 0;
                            @endphp

                            @foreach($requiredTypes as $type => $config)
                                @php
                                    $document = $documentsByType->get($type, collect())->first();
                                    $isCompleted = $document && $document->isApproved();
                                    if ($isCompleted) $completedRequired++;
                                @endphp
                                
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3">
                                        @if($isCompleted)
                                            <i class="fas fa-check-circle text-success"></i>
                                        @elseif($document && $document->isPending())
                                            <i class="fas fa-clock text-warning"></i>
                                        @elseif($document && $document->isRejected())
                                            <i class="fas fa-times-circle text-danger"></i>
                                        @else
                                            <i class="fas fa-upload text-muted"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-medium">{{ $config['label'] }}</span>
                                            @if($isCompleted)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($document && $document->isPending())
                                                <span class="badge bg-warning">Under Review</span>
                                            @elseif($document && $document->isRejected())
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="status-badge not-uploaded">
                                                    <i class="fas fa-upload"></i>
                                                    Not Uploaded
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <hr>

                            <!-- Overall Status -->
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if(auth()->user()->canPostJobs())
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Job Posting Status</span>
                                        @if(auth()->user()->canPostJobs())
                                            <span class="badge bg-success fs-6">Enabled</span>
                                        @else
                                            <span class="badge bg-warning fs-6">Pending Verification</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        @if(auth()->user()->canPostJobs())
                                            You can now post job openings on our platform
                                        @else
                                            Complete KYC and document verification to post jobs
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents List -->
            <div class="row">
                <div class="col-12">
                    <div class="documents-card">
                        <div class="card-header">
                            <h6>Uploaded Documents</h6>
                        </div>
                        <div class="card-body">
                            @if($documents->isEmpty())
                                <div class="empty-state-enhanced">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-file-upload"></i>
                                    </div>
                                    <div class="empty-state-title">No documents uploaded yet</div>
                                    <div class="empty-state-description">
                                        Upload your business documents to complete verification and unlock job posting features
                                    </div>
                                    <a href="{{ route('employer.documents.create') }}" class="empty-state-action">
                                        <i class="fas fa-plus"></i>
                                        Upload First Document
                                    </a>
                                </div>
                            @else
                                <div class="documents-table table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Document Type</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Submitted</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($documents as $document)
                                                <tr>
                                                    <td>
                                                        <div class="document-type-cell">
                                                            <div class="document-type-icon">
                                                                <i class="fas fa-file-alt"></i>
                                                            </div>
                                                            <div class="document-info">
                                                                <div class="document-name">{{ $document->document_type_config['label'] ?? ucfirst(str_replace('_', ' ', $document->document_type)) }}</div>
                                                                @if($document->document_type_config['required'] ?? false)
                                                                    <div class="required-label">
                                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                                        Required
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="document-info">
                                                            <div class="document-name">{{ $document->document_name }}</div>
                                                            <div class="document-meta">{{ $document->formatted_file_size }}</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($document->isApproved())
                                                            <span class="status-badge approved">
                                                                <i class="fas fa-check-circle"></i>
                                                                Approved
                                                            </span>
                                                        @elseif($document->isPending())
                                                            <span class="status-badge pending">
                                                                <i class="fas fa-clock"></i>
                                                                Under Review
                                                            </span>
                                                        @elseif($document->isRejected())
                                                            <span class="status-badge rejected">
                                                                <i class="fas fa-times-circle"></i>
                                                                Rejected
                                                            </span>
                                                        @else
                                                            <span class="status-badge not-uploaded">
                                                                <i class="fas fa-upload"></i>
                                                                {{ $document->status_label }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="date-cell">
                                                            <div class="date-main">{{ $document->submitted_at->format('M d, Y') }}</div>
                                                            <div class="date-time">{{ $document->submitted_at->format('g:i A') }}</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="{{ route('employer.documents.show', $document) }}" 
                                                               class="btn btn-outline-primary" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('employer.documents.download', $document) }}" 
                                                               class="btn btn-outline-success" title="Download">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            @if($document->isRejected())
                                                                <a href="{{ route('employer.documents.edit', $document) }}" 
                                                                   class="btn btn-outline-warning" title="Resubmit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @endif
                                                            @if(!$document->isApproved())
                                                                <button type="button" class="btn btn-outline-danger" 
                                                                        onclick="deleteDocument({{ $document->id }})" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if($document->isRejected() && $document->admin_notes)
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="alert alert-warning mb-0 ms-4">
                                                                <strong>Admin Notes:</strong> {{ $document->admin_notes }}
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
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this document? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
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
</script>
@endpush
