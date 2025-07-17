@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Manage Your Jobs</h1>
                        <p class="text-muted mb-0">Create, edit and track your job postings</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary d-flex align-items-center">
                            <i class="bi bi-plus-circle me-2"></i>Post New Job
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('employer.jobs.drafts') }}"><i class="bi bi-file-earmark me-2"></i>View Drafts</a></li>
                                <li><a class="dropdown-item" href="{{ route('employer.analytics.index') }}"><i class="bi bi-graph-up me-2"></i>View Analytics</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Jobs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary-subtle text-primary rounded-circle p-3 me-3">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Jobs</h6>
                        <h2 class="mb-0">{{ number_format($jobs->total()) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Jobs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success-subtle text-success rounded-circle p-3 me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Active Jobs</h6>
                        <h2 class="mb-0">{{ number_format($jobs->where('status', 1)->count()) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Applications -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-info-subtle text-info rounded-circle p-3 me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Applications</h6>
                        <h2 class="mb-0">{{ number_format($jobs->sum('applications_count')) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Applications -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stats-card bg-white rounded-3 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning-subtle text-warning rounded-circle p-3 me-3">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Avg. Applications</h6>
                        <h2 class="mb-0">{{ number_format($jobs->avg('applications_count'), 1) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="card-title mb-1">All Jobs</h5>
                    <div class="small text-muted">Manage and monitor your job postings</div>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search jobs by title..." id="searchInput">
                    </div>
                    <select class="form-select" id="statusFilter" style="width: auto;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($jobs->isEmpty())
                <div class="empty-state text-center py-5">
                    <img src="{{ asset('images/empty-jobs.svg') }}" alt="No Jobs" class="mb-4" style="max-width: 200px;">
                    <h3 class="h5 mb-3">No Jobs Posted Yet</h3>
                    <p class="text-muted mb-4">Start by posting your first job listing</p>
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Post Your First Job
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th class="text-center">Applications</th>
                                <th class="text-center">Views</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Posted Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobs as $job)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $job->title }}</h6>
                                            <div class="small text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $job->location }}
                                                <span class="mx-2">•</span>
                                                <i class="bi bi-briefcase me-1"></i>{{ $job->jobType->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('employer.applications.index', ['job_id' => $job->id]) }}" class="text-decoration-none">
                                        <span class="d-block fw-bold text-dark">{{ number_format($job->applications_count) }}</span>
                                        <small class="text-muted">View All</small>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="d-block fw-bold text-dark">{{ number_format($job->views_count) }}</span>
                                    <small class="text-muted">Views</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-{{ $job->status ? 'success' : 'danger' }}-subtle text-{{ $job->status ? 'success' : 'danger' }}">
                                        {{ $job->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="d-block">{{ $job->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $job->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('employer.jobs.edit', $job->id) }}" 
                                           class="btn btn-sm btn-light"
                                           data-bs-toggle="tooltip"
                                           title="Edit Job">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('employer.applications.index', ['job_id' => $job->id]) }}" 
                                           class="btn btn-sm btn-light"
                                           data-bs-toggle="tooltip"
                                           title="View Applications">
                                            <i class="bi bi-people"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-light"
                                                onclick="confirmDelete({{ $job->id }})"
                                                data-bs-toggle="tooltip"
                                                title="Delete Job">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($jobs->hasPages())
                <div class="d-flex justify-content-center border-top p-4">
                    {{ $jobs->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <i class="bi bi-exclamation-circle text-danger fa-3x"></i>
                </div>
                <h5 class="mb-2">Are you sure?</h5>
                <p class="text-muted mb-0">This action cannot be undone and will remove all associated applications.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Job</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
}

.stats-card {
    transition: all 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--bs-box-shadow);
}

.table > :not(caption) > * > * {
    padding: 1rem 1.25rem;
    background: none;
}

.table > thead > tr > th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #6c757d;
    background: #fff;
    border-bottom: 1px solid var(--border-color);
}

.badge.bg-success-subtle {
    background-color: var(--bs-success-bg-subtle);
    color: var(--bs-success);
}

.badge.bg-danger-subtle {
    background-color: var(--bs-danger-bg-subtle);
    color: var(--bs-danger);
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(jobId) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    form.action = `/employer/jobs/${jobId}`;
    new bootstrap.Modal(modal).show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    let searchTimeout;

    function performSearch() {
        const searchQuery = searchInput.value.trim();
        const status = statusFilter.value;
        const params = new URLSearchParams(window.location.search);
        
        if (searchQuery) params.set('search', searchQuery);
        else params.delete('search');
        
        if (status) params.set('status', status);
        else params.delete('status');

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500);
    });

    statusFilter?.addEventListener('change', performSearch);
});
</script>
@endpush
@endsection 