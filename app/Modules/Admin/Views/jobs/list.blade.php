@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Jobs Management</h1>
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
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
