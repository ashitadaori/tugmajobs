@extends('layouts.admin')

@section('page_title', 'Jobs Management')

@section('content')
<style>
/* CACHE BUSTER: {{ config('app.asset_version', 'v1') }} */
/* INLINE STYLE - HIGHEST PRIORITY - HIDE ARROWS */
.pagination svg,
.pagination path,
nav svg,
nav path {
    display: none !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
}
</style>

<script>
// CACHE BUSTER: {{ config('app.asset_version', 'v1') }}
// JAVASCRIPT - REMOVE ARROWS AFTER PAGE LOAD
document.addEventListener('DOMContentLoaded', function() {
    // Remove all SVG elements in pagination
    document.querySelectorAll('.pagination svg, nav svg').forEach(function(svg) {
        svg.remove();
    });
    
    // Also hide them with style
    document.querySelectorAll('.pagination svg, nav svg').forEach(function(svg) {
        svg.style.display = 'none';
        svg.style.visibility = 'hidden';
        svg.style.width = '0';
        svg.style.height = '0';
    });
    
    console.log('✅ Pagination arrows removed - Cache Version: {{ config('app.asset_version', 'v1') }}');
});
</script>

<div class="container-fluid">
    <!-- CACHE BUSTER VERIFICATION -->
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <strong>🚀 CACHE BUSTER ACTIVE!</strong> Version: <code>{{ config('app.asset_version', 'v1') }}</code> - Page loaded successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    
    <!-- Top Action Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Jobs Management</h2>
                    <p class="text-muted mb-0">Manage all job postings on the platform</p>
                </div>
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-lg" style="font-size: 1.2rem; padding: 1rem 2rem;">
                    <i class="bi bi-plus-circle me-2"></i>POST NEW JOB
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

    <!-- Jobs Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Jobs ({{ $jobs->total() }})</h5>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-primary active">All Jobs</a>
                    <a href="{{ route('admin.jobs.pending') }}" class="btn btn-outline-warning">Pending</a>
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
                            <th>Status</th>
                            <th>Posted</th>
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
                                    @if($job->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($job->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($job->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($job->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $job->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.jobs.show', $job) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.jobs.applicants', $job->id) }}" 
                                       class="btn btn-sm btn-outline-success"
                                       title="View Applicants">
                                        <i class="bi bi-people"></i> ({{ $job->applications_count ?? 0 }})
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No jobs found</p>
                                    <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Post Your First Job
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
                        {{ $jobs->links('vendor.pagination.simple-admin') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* Custom styles for this page */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group-sm .btn {
    font-size: 0.875rem;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
}

/* ULTRA AGGRESSIVE - REMOVE ALL ARROWS AND SVGS */
.pagination svg,
.pagination svg *,
nav svg,
nav svg *,
.page-link svg,
.page-link svg *,
.page-item svg,
.page-item svg * {
    display: none !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    opacity: 0 !important;
    position: absolute !important;
    left: -9999px !important;
}

/* Hide any ::before or ::after pseudo-elements that might contain arrows */
.pagination .page-link::before,
.pagination .page-link::after {
    content: none !important;
    display: none !important;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    color: #0d6efd !important;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: white !important;
}
</style>

<script>
// ULTRA AGGRESSIVE ARROW REMOVAL - Version {{ config('app.asset_version', 'v1') }}
(function() {
    function removeAllArrows() {
        // Remove all SVG elements
        document.querySelectorAll('.pagination svg, nav svg, .page-link svg, .page-item svg').forEach(function(svg) {
            svg.remove();
        });
        
        // Remove any elements that look like arrows
        document.querySelectorAll('.pagination *').forEach(function(el) {
            if (el.innerHTML && (el.innerHTML.includes('&lt;') || el.innerHTML.includes('&gt;') || 
                el.innerHTML.includes('←') || el.innerHTML.includes('→') ||
                el.innerHTML.includes('‹') || el.innerHTML.includes('›'))) {
                el.innerHTML = el.innerHTML.replace(/[<>←→‹›]/g, '');
            }
        });
        
        console.log('✅ Arrows removed - Version: {{ config('app.asset_version', 'v1') }}');
    }
    
    // Run immediately
    removeAllArrows();
    
    // Run after DOM loads
    document.addEventListener('DOMContentLoaded', removeAllArrows);
    
    // Run after page fully loads
    window.addEventListener('load', removeAllArrows);
    
    // Watch for any changes to pagination (in case it's dynamically loaded)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            removeAllArrows();
        });
        
        // Start observing after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const paginationElements = document.querySelectorAll('.pagination, nav');
            paginationElements.forEach(function(el) {
                observer.observe(el, { childList: true, subtree: true });
            });
        });
    }
})();
</script>
@endsection
