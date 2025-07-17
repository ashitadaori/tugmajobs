@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">My Job Applications</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('front.account.sidebar')
            </div>
            <div class="col-lg-9">
                @include('front.message')

                <div class="card border-0 shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fs-4 mb-0">Job Applications</h3>
                        </div>

                        @if ($jobApplications->isEmpty())
                            <div class="text-center py-5">
                                <img src="{{ asset('images/empty-applications.svg') }}" alt="No Applications" class="mb-4" style="max-width: 200px;">
                                <h5>No Job Applications Yet</h5>
                                <p class="text-muted">You haven't applied to any jobs yet. Start exploring opportunities!</p>
                                <a href="{{ route('jobs') }}" class="btn btn-primary">Browse Jobs</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Applied Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($jobApplications as $jobApplication)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-500 mb-1">{{ $jobApplication->job->title }}</span>
                                                        <small class="text-muted">
                                                            {{ $jobApplication->job->jobType->name }} • {{ $jobApplication->job->location }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span>{{ \Carbon\Carbon::parse($jobApplication->applied_date)->format('M d, Y') }}</span>
                                                        <small class="text-muted">{{ $jobApplication->job->applications->count() }} applicant(s)</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($jobApplication->status == 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($jobApplication->status == 'approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($jobApplication->status == 'rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm" type="button" id="actionDropdown{{ $jobApplication->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdown{{ $jobApplication->id }}">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('jobDetail', $jobApplication->job_id) }}">
                                                                    <i class="fas fa-eye me-2"></i> View Job
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button type="button" class="dropdown-item text-danger" onclick="confirmRemoveApplication({{ $jobApplication->id }})">
                                                                    <i class="fas fa-trash-alt me-2"></i> Withdraw Application
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                {{ $jobApplications->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJs')
<script>
function confirmRemoveApplication(id) {
    if (confirm("Are you sure you want to withdraw this application?")) {
        $.ajax({
            type: "POST",
            url: '{{ route("account.removeJobs") }}',
            data: {
                id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status) {
                    toastr.success('Application withdrawn successfully');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error('Failed to withdraw application. Please try again.');
                }
            },
            error: function() {
                toastr.error('An error occurred. Please try again.');
            }
        });
    }
}
</script>
@endsection


