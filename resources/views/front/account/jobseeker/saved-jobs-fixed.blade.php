@extends('layouts.jobseeker')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Saved Jobs</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('account.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Saved Jobs</li>
                        </ol>
                    </nav>
                </div>
                <div class="text-muted">
                    <i class="fas fa-bookmark me-2"></i>{{ $savedJobs->total() }} saved jobs
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Saved Jobs List -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($savedJobs->count() > 0)
                        @foreach($savedJobs as $savedJob)
                            @if($savedJob->job)
                                <div class="saved-job-item border-bottom p-4" data-job-id="{{ $savedJob->job->id }}">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-start">
                                                <div class="job-logo me-3">
                                                    @if($savedJob->job->employer && $savedJob->job->employer->image)
                                                        <img src="{{ asset('storage/' . $savedJob->job->employer->image) }}" 
                                                             alt="Company Logo" 
                                                             class="rounded" 
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center" 
                                                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                            {{ substr($savedJob->job->employer->name ?? 'C', 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2">
                                                        <a href="{{ route('jobDetail', $savedJob->job->id) }}" 
                                                           class="text-decoration-none text-dark">
                                                            {{ $savedJob->job->title }}
                                                        </a>
                                                    </h5>
                                                    <div class="text-muted mb-2">
                                                        <i class="fas fa-building me-1"></i>
                                                        {{ $savedJob->job->employer->name ?? 'Company' }}
                                                    </div>
                                                    <div class="job-details d-flex flex-wrap gap-3 text-muted small">
                                                        <span>
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            {{ $savedJob->job->location }}
                                                        </span>
                                                        <span>
                                                            <i class="fas fa-briefcase me-1"></i>
                                                            {{ $savedJob->job->jobType->name ?? 'N/A' }}
                                                        </span>
                                                        @if($savedJob->job->salary_min && $savedJob->job->salary_max)
                                                            <span>
                                                                <i class="fas fa-peso-sign me-1"></i>
                                                                ₱{{ number_format($savedJob->job->salary_min) }} - ₱{{ number_format($savedJob->job->salary_max) }}
                                                            </span>
                                                        @endif
                                                        <span>
                                                            <i class="fas fa-clock me-1"></i>
                                                            Saved {{ $savedJob->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="d-flex flex-column gap-2">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('jobDetail', $savedJob->job->id) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View Job
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm remove-saved-job"
                                                            data-job-id="{{ $savedJob->job->id }}"
                                                            title="Remove from saved">
                                                        <i class="fas fa-trash me-1"></i>Remove
                                                    </button>
                                                </div>
                                                @if($savedJob->job->applications_count > 0)
                                                    <small class="text-muted">
                                                        {{ $savedJob->job->applications_count }} applications
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-bookmark text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                            <h5 class="text-muted mb-3">No Saved Jobs Yet</h5>
                            <p class="text-muted mb-4">Start saving jobs you're interested in to view them here later.</p>
                            <a href="{{ route('jobs') }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Browse Jobs
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pagination -->
            @if($savedJobs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $savedJobs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove saved job functionality
    document.querySelectorAll('.remove-saved-job').forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job-id');
            
            if (confirm('Are you sure you want to remove this job from your saved list?')) {
                // Send AJAX request to remove
                fetch('{{ route("account.saved-jobs.destroy") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ job_id: jobId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the job item from the DOM
                        const jobItem = this.closest('.saved-job-item');
                        jobItem.style.transition = 'opacity 0.3s';
                        jobItem.style.opacity = '0';
                        setTimeout(() => {
                            jobItem.remove();
                            
                            // Check if there are no more jobs
                            const remainingJobs = document.querySelectorAll('.saved-job-item');
                            if (remainingJobs.length === 0) {
                                location.reload();
                            }
                        }, 300);
                        
                        // Show success message
                        showAlert('success', 'Job removed from saved list');
                    } else {
                        showAlert('danger', 'Failed to remove job. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred. Please try again.');
                });
            }
        });
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert at the top of the container
        const container = document.querySelector('.container-fluid .row .col-12');
        const firstChild = container.children[1]; // After the header
        firstChild.insertAdjacentHTML('beforebegin', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>

<style>
/* Improve text readability for saved jobs page */
.main-content {
    color: #1e293b !important;
}

.main-content h1, 
.main-content h2, 
.main-content h3, 
.main-content h4, 
.main-content h5, 
.main-content h6 {
    color: #1e293b !important;
    font-weight: 600;
}

.main-content .text-muted {
    color: #64748b !important;
}

.main-content .breadcrumb-item a {
    color: #6366f1 !important;
    text-decoration: none;
}

.main-content .breadcrumb-item.active {
    color: #64748b !important;
}

.saved-job-item {
    background: white !important;
    transition: all 0.3s ease;
}

.saved-job-item:hover {
    background: #f8f9fa !important;
}

.saved-job-item .job-title {
    color: #1e293b !important;
    font-weight: 600;
    font-size: 1.1rem;
}

.saved-job-item .company-name {
    color: #6366f1 !important;
    font-weight: 500;
}

.saved-job-item .job-location,
.saved-job-item .job-type,
.saved-job-item .job-salary {
    color: #64748b !important;
}

.saved-job-item h5 a {
    color: #1e293b !important;
    transition: color 0.2s;
}

.saved-job-item h5 a:hover {
    color: #6366f1 !important;
}

.btn-outline-primary {
    color: #6366f1 !important;
    border-color: #6366f1 !important;
}

.btn-outline-primary:hover {
    background-color: #6366f1 !important;
    color: white !important;
}

.btn-outline-danger {
    color: #dc2626 !important;
    border-color: #dc2626 !important;
}

.btn-outline-danger:hover {
    background-color: #dc2626 !important;
    color: white !important;
}

.alert {
    color: #1e293b !important;
}

.alert-success {
    background-color: #f0fdf4 !important;
    border-color: #bbf7d0 !important;
    color: #166534 !important;
}

.alert-danger {
    background-color: #fef2f2 !important;
    border-color: #fecaca !important;
    color: #dc2626 !important;
}
</style>
@endpush
