@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Job Applications</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('admin.sidebar')
            </div>
            <div class="col-lg-9">
                @include('front.message')
                <div class="card border-0 shadow mb-4">
                    <div class="card-body card-form">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="fs-4 mb-1">Job Applications</h3>
                            </div>
                            <div>
                                <select id="statusFilter" class="form-select form-select-sm" onchange="filterApplications()">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="reviewing">Reviewing</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">Job Title</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Employer</th>
                                        <th scope="col">Applied Date</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="border-0">
                                    @if ($jobApplications->isNotEmpty())
                                        @foreach ($jobApplications as $jobApplication)
                                        <tr class="align-middle">
                                            <td>
                                                <div class="job-name fw-500">{{ $jobApplication->job->title }}</div>
                                                <div class="info1">{{ $jobApplication->job->jobType->name }} • {{ $jobApplication->job->location }}</div>
                                            </td>
                                            <td>{{ $jobApplication->user->name }}</td>
                                            <td>{{ $jobApplication->employer->name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($jobApplication->applied_date)->format('d M, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $jobApplication->status == 'pending' ? 'warning' : ($jobApplication->status == 'approved' ? 'success' : ($jobApplication->status == 'reviewing' ? 'info' : 'danger')) }}">
                                                    {{ ucfirst($jobApplication->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.jobApplications.show', $jobApplication->id) }}">
                                                                <i class="fas fa-eye me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ asset('resumes/' . $jobApplication->resume) }}" target="_blank">
                                                                <i class="fas fa-download me-2"></i>Download Resume
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="removeApplication({{ $jobApplication->id }})">
                                                                <i class="fas fa-trash me-2"></i>Remove
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <img src="{{ asset('images/empty-applications.svg') }}" alt="No Applications" class="mb-3" style="max-width: 200px;">
                                                <h4>No Applications Found</h4>
                                                <p class="text-muted">There are no job applications to display.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $jobApplications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJs')
<script type="text/javascript">
function removeApplication(applicationId) {
    if(confirm('Are you sure you want to remove this application?')) {
        $.ajax({
            type: "DELETE",
            url: "{{ route('admin.jobApplications.destroy') }}",
            data: {
                _token: '{{ csrf_token() }}',
                job_id: applicationId
            },
            success: function(response) {
                if(response.status) {
                    // Show success message
                    showAlert('Application removed successfully', 'success');
                    // Reload the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(response.message || 'Error removing application', 'danger');
                }
            },
            error: function() {
                showAlert('Error removing application', 'danger');
            }
        });
    }
}

function filterApplications() {
    const status = $('#statusFilter').val();
    const currentUrl = new URL(window.location.href);
    
    if (status) {
        currentUrl.searchParams.set('status', status);
    } else {
        currentUrl.searchParams.delete('status');
    }
    
    window.location.href = currentUrl.toString();
}

// Set the status filter value from URL params
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status) {
        $('#statusFilter').val(status);
    }
});

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert before the card
    $('.card.border-0.shadow').before(alertHtml);
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);
}
</script>

<style>
.dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-item i {
    width: 1rem;
    text-align: center;
}

.badge {
    font-size: 0.85em;
    padding: 0.35em 0.65em;
}

.info1 {
    font-size: 0.875rem;
    color: #6c757d;
}

.btn-group .dropdown-toggle::after {
    margin-left: 0.5em;
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}
</style>
@endsection
