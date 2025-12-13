@extends('layouts.admin')

@section('page_title', 'Jobs Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Jobs Management</h1>
            <p class="text-muted mb-0">Manage all job postings</p>
        </div>
        <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle me-2"></i>Post New Job
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.jobs.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search jobs..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Approved</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="jobs-table-container">
                @include('admin.jobs.partials.jobs-table', ['jobs' => $jobs])
            </div>

            <!-- Pagination -->
            <div class="mt-4" id="jobs-pagination">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.required:after {
    content: " *";
    color: red;
}
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

/* FORCE SMALL PAGINATION ARROWS */
#jobs-pagination .pagination svg {
    width: 16px !important;
    height: 16px !important;
    max-width: 16px !important;
    max-height: 16px !important;
}

#jobs-pagination .pagination .page-link {
    padding: 0.5rem 0.75rem !important;
    font-size: 0.875rem !important;
    min-width: 40px !important;
    height: 40px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#jobs-pagination .pagination .page-item {
    margin: 0 3px !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    
    // Handle form submission for filtering
    $('form[action*="admin.jobs.index"]').on('submit', function(e) {
        e.preventDefault();
        loadJobs();
    });
    
    // Handle real-time search with debounce
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadJobs();
        }, 500); // 500ms delay
    });
    
    // Handle status filter change
    $('select[name="status"]').on('change', function() {
        loadJobs();
    });
    
    // Handle pagination clicks
    $(document).on('click', '#jobs-pagination .pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            loadJobsFromUrl(url);
        }
    });
    
    function loadJobs() {
        const formData = $('form[action*="admin.jobs.index"]').serialize();
        const url = '{{ route("admin.jobs.index") }}?' + formData;
        loadJobsFromUrl(url);
    }
    
    function loadJobsFromUrl(url) {
        showLoading();
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#jobs-table-container').html(response.html);
                $('#jobs-pagination').html(response.pagination);
                hideLoading();
            },
            error: function(xhr, status, error) {
                console.error('Error loading jobs:', error);
                hideLoading();
                
                // Show error message
                $('#jobs-table-container').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fas fa-exclamation-triangle me-2"></i>' +
                    'Error loading jobs. Please refresh the page and try again.' +
                    '</div>'
                );
            }
        });
    }
    
    function showLoading() {
        if ($('#jobs-table-container .loading-overlay').length === 0) {
            $('#jobs-table-container').css('position', 'relative').append(
                '<div class="loading-overlay">' +
                '<div class="spinner-border text-primary" role="status">' +
                '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '</div>'
            );
        }
    }
    
    function hideLoading() {
        $('#jobs-table-container .loading-overlay').remove();
        $('#jobs-table-container').css('position', '');
    }
});
</script>
@endpush
@endsection
