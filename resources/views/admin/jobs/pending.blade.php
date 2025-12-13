@extends('layouts.admin')

@section('page_title', 'Pending Jobs')

@section('content')
<div class="container-fluid">
    <!-- Top Action Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Pending Jobs</h2>
                    <p class="text-muted mb-0">Review and approve job postings</p>
                </div>
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Post New Job
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Pending Jobs Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history text-warning me-2"></i>
                    Pending Jobs ({{ $jobs->total() }})
                </h5>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-primary">All Jobs</a>
                    <a href="{{ route('admin.jobs.pending') }}" class="btn btn-outline-warning active">Pending</a>
                    <a href="{{ route('admin.jobs.create') }}" class="btn btn-success">
                        <i class="bi bi-plus"></i> New
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Submitted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $job->title }}</div>
                                    @if($job->posted_by_admin ?? false)
                                        <span class="badge bg-info text-white" style="font-size: 0.7rem;">
                                            <i class="bi bi-shield-check"></i> Admin Posted
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $job->employer->name ?? 'N/A' }}</td>
                                <td>{{ $job->category->name ?? 'N/A' }}</td>
                                <td>{{ $job->jobType->name ?? 'N/A' }}</td>
                                <td>
                                    <small class="text-muted">{{ $job->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.jobs.show', $job) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="View & Review">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                                    <p class="text-muted mt-2">No pending jobs! All caught up.</p>
                                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-primary">
                                        View All Jobs
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        @if($jobs->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $jobs->firstItem() }} to {{ $jobs->lastItem() }} of {{ $jobs->total() }} jobs
                    </div>
                    <nav>
                        {{ $jobs->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* Custom styles */
.table-hover tbody tr:hover {
    background-color: #fff3cd;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endsection
