@extends('layouts.admin')

@section('page_title', 'Manual KYC Documents')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <i class="fas fa-file-alt me-2"></i>
            Manual KYC Documents Review
        </h1>
        @if($pendingCount > 0)
            <span class="badge bg-warning fs-6">{{ $pendingCount }} Pending</span>
        @endif
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

    <!-- Info Card -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Manual KYC Review:</strong> This section contains identity documents submitted by users with Philippine IDs or other documents not supported by the automated DiDit verification system. Please carefully review each document before approving or rejecting.
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kyc.manual-documents') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="user_type" class="form-label">User Type</label>
                        <select name="user_type" id="user_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="jobseeker" {{ request('user_type') === 'jobseeker' ? 'selected' : '' }}>Jobseeker</option>
                            <option value="employer" {{ request('user_type') === 'employer' ? 'selected' : '' }}>Employer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="document_type" class="form-label">Document Type</label>
                        <select name="document_type" id="document_type" class="form-select">
                            <option value="">All Documents</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type }}" {{ request('document_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search User</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Name or Email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.kyc.manual-documents') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Document Type</th>
                            <th>Document Number</th>
                            <th>Status</th>
                            <th>Submitted Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            @php
                                $statusColor = match($document->status) {
                                    'verified' => 'success',
                                    'rejected' => 'danger',
                                    default => 'warning'
                                };
                                $files = json_decode($document->document_file, true);
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            @if($document->user->profile_image)
                                                <img src="{{ $document->user->profile_image }}"
                                                     class="rounded-circle"
                                                     width="40" height="40"
                                                     alt="{{ $document->user->name }}">
                                            @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px; font-size: 18px;">
                                                    {{ strtoupper(substr($document->user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $document->user->name }}</div>
                                            <div class="text-muted small">{{ $document->user->email }}</div>
                                            <span class="badge bg-{{ $document->user->role === 'employer' ? 'info' : 'primary' }} small">
                                                {{ ucfirst($document->user->role ?? 'User') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $document->document_type }}</span>
                                </td>
                                <td>
                                    @if($document->document_number)
                                        <code>{{ $document->document_number }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst($document->status) }}
                                    </span>
                                    @if($document->status === 'rejected' && $document->rejection_reason)
                                        <i class="fas fa-info-circle text-muted ms-1"
                                           data-bs-toggle="tooltip"
                                           title="{{ $document->rejection_reason }}"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        <div>{{ $document->created_at->format('M j, Y') }}</div>
                                        <div class="text-muted">{{ $document->created_at->format('g:i A') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <!-- View Details Button -->
                                        <a href="{{ route('admin.kyc.show-manual-document', $document) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Quick View Document Images -->
                                        @if(is_array($files))
                                            <div class="btn-group" role="group">
                                                @if(!empty($files['front']))
                                                    <a href="{{ route('kyc.manual.view', [$document, 'front']) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="View Front" target="_blank">
                                                        <i class="fas fa-image"></i>
                                                    </a>
                                                @endif
                                                @if(!empty($files['back']))
                                                    <a href="{{ route('kyc.manual.view', [$document, 'back']) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="View Back" target="_blank">
                                                        <i class="fas fa-id-card"></i>
                                                    </a>
                                                @endif
                                                @if(!empty($files['selfie']))
                                                    <a href="{{ route('kyc.manual.view', [$document, 'selfie']) }}"
                                                       class="btn btn-sm btn-outline-warning"
                                                       title="View Selfie" target="_blank">
                                                        <i class="fas fa-camera"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif

                                        @if($document->status === 'pending')
                                            <!-- Approve Button -->
                                            <form action="{{ route('admin.kyc.verify-manual-document', $document) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to approve this document?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>

                                            <!-- Reject Button -->
                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal{{ $document->id }}"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            @if($document->status === 'pending')
                            <div class="modal fade" id="rejectModal{{ $document->id }}" tabindex="-1">
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
                                                    <textarea name="rejection_reason" class="form-control" rows="3" required
                                                              placeholder="Please provide a clear reason for rejection so the user can resubmit with correct documents..."></textarea>
                                                    <div class="form-text">This reason will be shared with the user.</div>
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
                            @endif
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                                    <p class="mb-2">No manual KYC documents found.</p>
                                    @if(request()->hasAny(['status', 'search', 'user_type', 'document_type']))
                                        <a href="{{ route('admin.kyc.manual-documents') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-times me-1"></i>Clear Filters
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($documents->hasPages())
                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.user-avatar img,
.user-avatar div {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-top: none;
}

.badge {
    font-size: 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn-group .btn {
    padding: 0.25rem 0.4rem;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
