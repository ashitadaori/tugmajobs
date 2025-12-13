@extends('layouts.admin')

@section('title', 'Document Review')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.employers.documents.index') }}">Employer Documents</a></li>
                    <li class="breadcrumb-item active">Review Document</li>
                </ol>
            </nav>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Document Review
                    </h3>
                    <div>
                        <span class="badge {{ $document->status_badge_class }} fs-6">
                            {{ $document->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <div class="row">
                        <!-- Document Information -->
                        <div class="col-lg-4">
                            <div class="card border-0 bg-light">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Document Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Employer:</strong><br>
                                        <div class="d-flex align-items-center mt-1">
                                            {{ $document->user->name }}
                                            @if($document->user->isKycVerified())
                                                <i class="fas fa-check-circle text-success ms-2" title="KYC Verified" data-bs-toggle="tooltip"></i>
                                            @else
                                                <i class="fas fa-exclamation-circle text-warning ms-2" title="KYC Pending" data-bs-toggle="tooltip"></i>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $document->user->email }}</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Document Type:</strong><br>
                                        @php
                                            $docConfig = $document->document_type_config;
                                        @endphp
                                        <span class="badge bg-info mt-1">{{ $docConfig['label'] ?? ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                                        @if($docConfig['required'] ?? false)
                                            <br><small class="text-danger">*Required Document</small>
                                        @endif
                                        @if(isset($docConfig['description']))
                                            <br><small class="text-muted">{{ $docConfig['description'] }}</small>
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Document Name:</strong><br>
                                        {{ $document->document_name }}
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Submitted:</strong><br>
                                        {{ $document->submitted_at?->format('F d, Y') }}<br>
                                        <small class="text-muted">{{ $document->submitted_at?->format('g:i A') }}</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>File Details:</strong><br>
                                        Size: {{ $document->formatted_file_size }}<br>
                                        <small class="text-muted">{{ $document->mime_type }}</small>
                                    </div>
                                    
                                    @if($document->reviewed_at)
                                    <div class="mb-3">
                                        <strong>Reviewed:</strong><br>
                                        {{ $document->reviewed_at->format('F d, Y g:i A') }}<br>
                                        <small class="text-muted">by {{ $document->reviewer->name }}</small>
                                    </div>
                                    @endif
                                    
                                    @if($document->admin_notes)
                                    <div class="mb-3">
                                        <strong>Admin Notes:</strong><br>
                                        <div class="bg-white p-2 rounded border">
                                            {{ $document->admin_notes }}
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="d-grid">
                                        <a href="{{ $document->file_url }}" 
                                           target="_blank" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-download me-2"></i>Download File
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Document Preview -->
                        <div class="col-lg-8">
                            <div class="card border-0">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-eye me-2"></i>Document Preview
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    @if(in_array($document->mime_type, ['application/pdf']))
                                        <iframe src="{{ $document->file_url }}" 
                                                width="100%" 
                                                height="600px" 
                                                style="border: none;">
                                        </iframe>
                                    @elseif(str_starts_with($document->mime_type, 'image/'))
                                        <div class="text-center p-3">
                                            <img src="{{ $document->file_url }}" 
                                                 class="img-fluid" 
                                                 style="max-height: 600px;" 
                                                 alt="Document preview">
                                        </div>
                                    @else
                                        <div class="text-center p-5">
                                            <i class="fas fa-file fa-3x text-muted mb-3"></i>
                                            <h5>Preview not available</h5>
                                            <p class="text-muted">This file type cannot be previewed. Please download to view.</p>
                                            <a href="{{ $document->file_url }}" 
                                               target="_blank" 
                                               class="btn btn-primary">
                                                <i class="fas fa-download me-2"></i>Download File
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($document->isPending())
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('admin.employers.documents.approve', $document) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to approve this document?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check me-2"></i>Approve Document
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <button type="button" 
                                    class="btn btn-danger btn-lg w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#rejectModal">
                                <i class="fas fa-times me-2"></i>Reject Document
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="card-footer bg-light text-center">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        This document has already been reviewed and cannot be modified.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.employers.documents.reject', $document) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Reject Document
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>You are about to reject this document.</strong><br>
                        Please provide a clear reason so the employer can understand what needs to be corrected.
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">
                            <strong>Rejection Reason <span class="text-danger">*</span></strong>
                        </label>
                        <textarea name="admin_notes" 
                                  id="admin_notes" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Please specify what's wrong with this document and what the employer needs to do to fix it..."
                                  required></textarea>
                        <div class="form-text">Be specific about issues such as image quality, document completeness, validity, etc.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Reject Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection

