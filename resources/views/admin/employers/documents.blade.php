@extends('layouts.admin')

@section('title', 'Employer Documents')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Employer Document Review</h3>
                    <div class="d-flex gap-2">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="statusFilter" id="all" value="all" checked>
                            <label class="btn btn-outline-secondary" for="all">All</label>
                            
                            <input type="radio" class="btn-check" name="statusFilter" id="pending" value="pending">
                            <label class="btn btn-outline-warning" for="pending">Pending</label>
                            
                            <input type="radio" class="btn-check" name="statusFilter" id="approved" value="approved">
                            <label class="btn btn-outline-success" for="approved">Approved</label>
                            
                            <input type="radio" class="btn-check" name="statusFilter" id="rejected" value="rejected">
                            <label class="btn btn-outline-danger" for="rejected">Rejected</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if($documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Employer</th>
                                        <th>Email</th>
                                        <th>Document Type</th>
                                        <th>Document Name</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>File Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                    <tr data-status="{{ $document->status }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <strong>{{ $document->user->name }}</strong>
                                                    @if($document->user->isKycVerified())
                                                        <i class="fas fa-check-circle text-success ms-1" title="KYC Verified" data-bs-toggle="tooltip"></i>
                                                    @else
                                                        <i class="fas fa-exclamation-circle text-warning ms-1" title="KYC Pending" data-bs-toggle="tooltip"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $document->user->email }}</td>
                                        <td>
                                            @php
                                                $docConfig = $document->document_type_config;
                                            @endphp
                                            <span class="badge bg-info">{{ $docConfig['label'] ?? ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                                            @if($docConfig['required'] ?? false)
                                                <small class="text-danger">*Required</small>
                                            @endif
                                        </td>
                                        <td>{{ $document->document_name }}</td>
                                        <td>
                                            <span class="badge {{ $document->status_badge_class }}">
                                                {{ $document->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $document->submitted_at?->format('M d, Y') }}<br>
                                            {{ $document->submitted_at?->format('g:i A') }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $document->formatted_file_size }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.employers.documents.show', $document) }}" 
                                                   class="btn btn-primary btn-sm"
                                                   title="View & Review">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($document->isPending())
                                                    <form action="{{ route('admin.employers.documents.approve', $document) }}" 
                                                          method="POST" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Are you sure you want to approve this document?')">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-success btn-sm"
                                                                title="Quick Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ $document->file_url }}" 
                                                   target="_blank" 
                                                   class="btn btn-info btn-sm"
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No documents found</h5>
                            <p class="text-muted">Employer documents will appear here when submitted for review.</p>
                        </div>
                    @endif
                </div>
                @if($documents->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $documents->firstItem() }} to {{ $documents->lastItem() }} of {{ $documents->total() }} results
                        </div>
                        {{ $documents->links() }}
                    </div>
                </div>
                @endif
            </div>
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
    
    // Status filter functionality
    const statusFilters = document.querySelectorAll('input[name="statusFilter"]');
    const tableRows = document.querySelectorAll('tbody tr');
    
    statusFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            const selectedStatus = this.value;
            
            tableRows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                
                if (selectedStatus === 'all' || rowStatus === selectedStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush
@endsection

