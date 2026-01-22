@extends('layouts.employer')

@section('title', 'View Document')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mb-4">
                <a href="{{ route('employer.documents.index') }}"
                    class="text-decoration-none text-muted small hover-primary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Documents
                </a>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <!-- Document Info Card -->
                    <div class="ep-card h-100">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">Document Details</h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="mb-4">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Document Name</label>
                                <div class="fw-medium text-dark">{{ $document->document_name }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Document Type</label>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="me-2">{{ $document->document_type_config['label'] ?? ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                                    @if($document->document_type_config['required'] ?? false)
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill"
                                            style="font-size: 0.65rem;">Required</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Status</label>
                                <div>
                                    @if($document->isApproved())
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </span>
                                    @elseif($document->isPending())
                                        <span
                                            class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                            <i class="bi bi-hourglass-split me-1"></i> Under Review
                                        </span>
                                    @elseif($document->isRejected())
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2">
                                            <i class="bi bi-x-circle me-1"></i> Rejected
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2">
                                            {{ $document->status_label }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Submitted On</label>
                                <div>
                                    <div>{{ $document->submitted_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $document->submitted_at->format('g:i A') }}</small>
                                </div>
                            </div>

                            <!-- Admin Notes (Visible only if rejected) -->
                            @if($document->isRejected() && $document->admin_notes)
                                <div class="alert alert-danger border-danger-subtle bg-danger-subtle text-danger mb-4">
                                    <h6 class="alert-heading fw-semibold mb-1">
                                        <i class="bi bi-exclamation-circle me-1"></i> Admin Notes
                                    </h6>
                                    <p class="mb-0 small">{{ $document->admin_notes }}</p>
                                </div>
                            @endif

                            <hr class="text-muted opacity-25 my-4">

                            <div class="d-grid gap-2">
                                <a href="{{ route('employer.documents.download', $document) }}"
                                    class="ep-btn ep-btn-primary">
                                    <i class="bi bi-download me-2"></i> Download File
                                </a>

                                @if($document->isRejected())
                                    <a href="{{ route('employer.documents.edit', $document) }}" class="ep-btn ep-btn-outline">
                                        <i class="bi bi-pencil me-2"></i> Edit / Resubmit
                                    </a>
                                @endif

                                @if(!$document->isApproved())
                                    <button type="button" class="ep-btn ep-btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal">
                                        <i class="bi bi-trash me-2"></i> Delete Document
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- File Preview Card -->
                    <div class="ep-card h-100">
                        <div class="ep-card-header d-flex justify-content-between align-items-center">
                            <h3 class="ep-card-title">File Preview</h3>
                            <div class="text-muted small">
                                {{ $document->file_name }} ({{ $document->formatted_file_size }})
                            </div>
                        </div>
                        <div class="ep-card-body p-0 d-flex align-items-center justify-content-center bg-light"
                            style="min-height: 500px;">
                            @php
                                $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                $fileUrl = Storage::url($document->file_path);
                            @endphp

                            @if($extension === 'pdf')
                                <iframe src="{{ $fileUrl }}" width="100%" height="600px" style="border: none;">
                                    This browser does not support PDFs. Please download the PDF to view it: <a
                                        href="{{ $fileUrl }}">Download PDF</a>
                                </iframe>
                            @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']))
                                <img src="{{ $fileUrl }}" class="img-fluid" style="max-height: 600px;"
                                    alt="{{ $document->document_name }}">
                            @else
                                <div class="text-center p-5">
                                    <div class="mb-3 text-muted opacity-50">
                                        <i class="bi bi-file-earmark-text display-1"></i>
                                    </div>
                                    <h5 class="text-muted">Preview not available for this file type</h5>
                                    <a href="{{ route('employer.documents.download', $document) }}"
                                        class="ep-btn ep-btn-outline mt-3">
                                        Download to View
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
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
                    <form action="{{ route('employer.documents.destroy', $document) }}" method="POST"
                        style="display: inline;">
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
@endsection

@push('styles')
    <style>
        .hover-primary:hover {
            color: var(--ep-primary) !important;
        }
    </style>
@endpush