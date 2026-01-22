@extends('layouts.admin')

@section('page_title', 'Jobs Posted')

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
    <!-- Top Action Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold">Jobs Posted</h2>
                    <p class="text-muted mb-0">Manage and post job opportunities on your platform</p>
                </div>
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-dark px-4 py-2">
                    <i class="bi bi-plus me-2"></i>Post New Job
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
        <div class="card-header bg-light py-4">
            <!-- Full Width Search Bar -->
            <div class="position-relative mb-3">
                <input type="text"
                       id="live-search"
                       class="form-control form-control-lg rounded-pill"
                       placeholder="Search jobs by title or company..."
                       style="padding-left: 45px; padding-right: 45px;"
                       autocomplete="off">
                <i class="bi bi-search position-absolute" style="left: 18px; top: 50%; transform: translateY(-50%); color: #6c757d; font-size: 1.1rem;"></i>
                <span id="search-spinner" class="position-absolute d-none" style="right: 18px; top: 50%; transform: translateY(-50%);">
                    <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                </span>
                <button type="button" id="clear-search" class="btn btn-link btn-sm position-absolute d-none p-0" style="right: 18px; top: 50%; transform: translateY(-50%); color: #6c757d;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Filter Buttons -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.jobs.index') }}" class="btn {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-4">All Jobs</a>
                <a href="{{ route('admin.jobs.index', ['status' => 'new']) }}" class="btn {{ request('status') == 'new' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-4">New Jobs</a>
                <a href="{{ route('admin.jobs.index', ['status' => 'pending']) }}" class="btn {{ request('status') == 'pending' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-4">Pending Jobs</a>
                <a href="{{ route('admin.jobs.index', ['status' => 'active']) }}" class="btn {{ request('status') == 'active' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-4">Active Jobs</a>

                <!-- Hidden status filter for JS compatibility -->
                <select id="status-filter" class="d-none">
                    <option value="">All Status</option>
                    <option value="0">Pending</option>
                    <option value="1">Approved</option>
                    <option value="2">Rejected</option>
                    <option value="3">Expired</option>
                    <option value="4">Closed</option>
                </select>
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
                    <tbody id="jobs-table-body">
                        @include('admin.jobs.partials.jobs-table-rows', ['jobs' => $jobs])
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-white" id="pagination-container">
            @if($jobs->hasPages())
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small" id="pagination-info">
                        Showing {{ $jobs->firstItem() }} to {{ $jobs->lastItem() }} of {{ $jobs->total() }} jobs
                    </div>
                    <nav id="pagination-links">
                        {{ $jobs->links('vendor.pagination.simple-admin') }}
                    </nav>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small" id="pagination-info">
                        Showing {{ $jobs->count() }} of {{ $jobs->total() }} jobs
                    </div>
                    <nav id="pagination-links"></nav>
                </div>
            @endif
        </div>
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

<!-- Live Search JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('live-search');
    const statusFilter = document.getElementById('status-filter');
    const tableBody = document.getElementById('jobs-table-body');
    const jobsTotal = document.getElementById('jobs-total');
    const paginationInfo = document.getElementById('pagination-info');
    const paginationLinks = document.getElementById('pagination-links');
    const searchSpinner = document.getElementById('search-spinner');
    const clearSearchBtn = document.getElementById('clear-search');

    let searchTimeout = null;
    let currentRequest = null;

    // Function to perform the search
    function performSearch() {
        const query = searchInput.value.trim();
        const status = statusFilter.value;

        // Show/hide clear button
        if (query.length > 0) {
            clearSearchBtn.classList.remove('d-none');
            searchSpinner.classList.add('d-none');
        } else {
            clearSearchBtn.classList.add('d-none');
        }

        // Show loading spinner
        searchSpinner.classList.remove('d-none');
        clearSearchBtn.classList.add('d-none');

        // Cancel previous request if any
        if (currentRequest) {
            currentRequest.abort();
        }

        // Create new request
        currentRequest = new AbortController();

        // Build the URL with query parameters
        const params = new URLSearchParams();
        if (query) params.append('q', query);
        if (status !== '') params.append('status', status);

        const url = '{{ route("admin.jobs.search") }}?' + params.toString();

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            signal: currentRequest.signal
        })
        .then(response => response.json())
        .then(data => {
            // Update table body
            tableBody.innerHTML = data.html;

            // Update total count
            jobsTotal.textContent = data.total;

            // Update pagination info
            if (data.from && data.to) {
                paginationInfo.textContent = `Showing ${data.from} to ${data.to} of ${data.total} jobs`;
            } else {
                paginationInfo.textContent = `Showing 0 of ${data.total} jobs`;
            }

            // Update pagination links
            paginationLinks.innerHTML = data.pagination;

            // Hide spinner, show clear button if there's text
            searchSpinner.classList.add('d-none');
            if (query.length > 0) {
                clearSearchBtn.classList.remove('d-none');
            }

            // Remove arrows from new pagination
            document.querySelectorAll('.pagination svg, nav svg').forEach(function(svg) {
                svg.remove();
            });
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
            }
            searchSpinner.classList.add('d-none');
            if (query.length > 0) {
                clearSearchBtn.classList.remove('d-none');
            }
        });
    }

    // Debounced search on input
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);

        // Debounce: wait 300ms after user stops typing
        searchTimeout = setTimeout(performSearch, 300);
    });

    // Immediate search on status filter change
    statusFilter.addEventListener('change', function() {
        clearTimeout(searchTimeout);
        performSearch();
    });

    // Clear search button
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.classList.add('d-none');
        performSearch();
        searchInput.focus();
    });

    // Search on Enter key (optional, for immediate search)
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();
        }
    });

    // Keyboard shortcut: Ctrl+K or Cmd+K to focus search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }

        // Escape to clear and blur search
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            clearSearchBtn.classList.add('d-none');
            searchInput.blur();
            performSearch();
        }
    });
});
</script>
@endsection
