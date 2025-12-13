@extends('layouts.admin')

@section('page_title', 'My Posted Jobs')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">My Posted Jobs</h2>
                    <p class="text-muted mb-0">Manage jobs you've posted and view applications</p>
                </div>
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Post New Job
                </a>
            </div>
        </div>
    </div>

    @if($jobs->count() > 0)
        <div class="row">
            @foreach($jobs as $job)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm hover-card {{ $job->trashed() ? 'border-danger opacity-75' : '' }}">
                        <div class="card-body">
                            @if($job->trashed())
                                <div class="alert alert-danger py-1 px-2 mb-2 small">
                                    <i class="bi bi-trash"></i> This job has been deleted
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">{{ $job->title }}</h5>
                                @if($job->trashed())
                                    <span class="badge bg-danger">Deleted</span>
                                @elseif($job->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <p class="text-muted mb-2">
                                    <i class="bi bi-building"></i> 
                                    @if($job->company)
                                        {{ $job->company->name }}
                                    @elseif($job->company_name)
                                        {{ $job->company_name }}
                                    @else
                                        Confidential
                                    @endif
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> {{ $job->location }}
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-tag"></i> {{ $job->category->name ?? 'N/A' }}
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-briefcase"></i> {{ $job->jobType->name ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="row text-center mb-3 py-3 bg-light rounded">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h4 class="mb-0 text-primary">{{ $job->applications_count }}</h4>
                                        <small class="text-muted">Applications</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-0 text-success">{{ $job->vacancy }}</h4>
                                    <small class="text-muted">Positions</small>
                                </div>
                            </div>

                            <p class="text-muted small mb-3">
                                <i class="bi bi-calendar"></i> Posted {{ $job->created_at->diffForHumans() }}
                            </p>

                            <div class="d-grid gap-2">
                                @if($job->trashed())
                                    {{-- Deleted job actions --}}
                                    <button type="button"
                                            class="btn btn-success restore-job-btn"
                                            data-job-id="{{ $job->id }}"
                                            data-job-title="{{ $job->title }}">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore Job
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm delete-job-btn"
                                            data-job-id="{{ $job->id }}"
                                            data-job-title="{{ $job->title }}"
                                            data-force="true">
                                        <i class="bi bi-trash"></i> Permanently Delete
                                    </button>
                                @else
                                    {{-- Active job actions --}}
                                    <a href="{{ route('admin.jobs.applicants', $job->id) }}"
                                       class="btn btn-primary">
                                        <i class="bi bi-people"></i> View Applications ({{ $job->applications_count }})
                                    </a>
                                    <div class="btn-group">
                                        <a href="{{ route('jobDetail', $job->id) }}"
                                           class="btn btn-outline-secondary btn-sm"
                                           target="_blank">
                                            <i class="bi bi-eye"></i> View Job
                                        </a>
                                        <a href="{{ route('admin.jobs.edit', $job->id) }}"
                                           class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm delete-job-btn"
                                                data-job-id="{{ $job->id }}"
                                                data-job-title="{{ $job->title }}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($jobs->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $jobs->links() }}
            </div>
        @endif
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-briefcase" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 mb-2">No Jobs Posted Yet</h4>
                <p class="text-muted mb-4">Start posting jobs to attract talented candidates</p>
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Post Your First Job
                </a>
            </div>
        </div>
    @endif
</div>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card {
    border: none;
    border-radius: 0.5rem;
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Delete job handler
    $('.delete-job-btn').on('click', function() {
        const jobId = $(this).data('job-id');
        const jobTitle = $(this).data('job-title');
        const isForce = $(this).data('force') === true;
        const card = $(this).closest('.col-md-6');

        const message = isForce
            ? `Are you sure you want to <strong>permanently delete</strong> "${jobTitle}"?<br><br>This will:<br>• Permanently remove the job<br>• Delete all applications<br>• <strong>This action cannot be undone!</strong>`
            : `Are you sure you want to delete <strong>"${jobTitle}"</strong>?<br><br>This will:<br>• Remove the job from job browser<br>• You can restore it later if needed`;

        Swal.fire({
            title: isForce ? 'Permanently Delete Job?' : 'Delete Job?',
            html: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: isForce ? 'Yes, Delete Permanently' : 'Yes, Delete It',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/admin/jobs/${jobId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (isForce) {
                                // Remove card completely for permanent delete
                                card.fadeOut(400, function() {
                                    $(this).remove();
                                    if ($('.col-md-6').length === 0) {
                                        location.reload();
                                    }
                                });
                            } else {
                                // Reload page to show deleted status
                                location.reload();
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: isForce ? 'Job has been permanently deleted' : 'Job has been deleted. You can restore it if needed.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete job'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the job'
                        });
                    }
                });
            }
        });
    });

    // Restore job handler
    $('.restore-job-btn').on('click', function() {
        const jobId = $(this).data('job-id');
        const jobTitle = $(this).data('job-title');

        Swal.fire({
            title: 'Restore Job?',
            html: `Are you sure you want to restore <strong>"${jobTitle}"</strong>?<br><br>The job will be visible again in the job browser.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Restore It',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Restoring...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/admin/jobs/${jobId}/restore`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Restored!',
                                text: 'Job has been restored successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to restore job'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while restoring the job'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection
