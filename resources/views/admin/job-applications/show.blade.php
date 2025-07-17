@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.jobApplications') }}">Job Applications</a></li>
                        <li class="breadcrumb-item active">Application Details</li>
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
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fs-4 mb-0">Application Details</h3>
                            <div class="status-badge">
                                Status: 
                                <span class="badge bg-{{ $application->status == 'pending' ? 'warning' : ($application->status == 'approved' ? 'success' : 'danger') }}">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Job Details -->
                        <div class="mb-4">
                            <h5 class="card-title">Job Information</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>{{ $application->job->title }}</h6>
                                    <p class="mb-1"><strong>Company:</strong> {{ $application->employer->name }}</p>
                                    <p class="mb-1"><strong>Location:</strong> {{ $application->job->location }}</p>
                                    <p class="mb-0"><strong>Job Type:</strong> {{ $application->job->jobType->name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Applicant Details -->
                        <div class="mb-4">
                            <h5 class="card-title">Applicant Information</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-1"><strong>Name:</strong> {{ $application->user->name }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $application->user->email }}</p>
                                    <p class="mb-1"><strong>Applied Date:</strong> {{ \Carbon\Carbon::parse($application->applied_date)->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cover Letter -->
                        @if($application->cover_letter)
                        <div class="mb-4">
                            <h5 class="card-title">Cover Letter</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    {{ $application->cover_letter }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Resume -->
                        <div class="mb-4">
                            <h5 class="card-title">Resume</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <a href="{{ asset('resumes/' . $application->resume) }}" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-download me-2"></i>Download Resume
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Application Status -->
                        <div class="mb-4">
                            <h5 class="card-title">Update Status</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <form id="statusUpdateForm">
                                        @csrf
                                        <div class="row align-items-end">
                                            <div class="col-md-6">
                                                <label class="form-label">Application Status</label>
                                                <select class="form-select" name="status" id="applicationStatus">
                                                    <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="reviewing" {{ $application->status == 'reviewing' ? 'selected' : '' }}>Reviewing</option>
                                                    <option value="approved" {{ $application->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-primary" onclick="updateApplicationStatus({{ $application->id }})">
                                                    Update Status
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Status History -->
                        @if($application->statusHistory->isNotEmpty())
                        <div>
                            <h5 class="card-title">Status History</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="timeline">
                                        @foreach($application->statusHistory->sortByDesc('created_at') as $history)
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-{{ $history->status == 'pending' ? 'warning' : ($history->status == 'approved' ? 'success' : 'danger') }}"></div>
                                            <div class="timeline-content">
                                                <h6 class="mb-0">{{ ucfirst($history->status) }}</h6>
                                                <p class="text-muted mb-0">{{ $history->created_at->format('M d, Y h:i A') }}</p>
                                                @if($history->notes)
                                                <p class="mb-0">{{ $history->notes }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
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
function updateApplicationStatus(applicationId) {
    const status = $('#applicationStatus').val();
    
    $.ajax({
        url: "{{ route('admin.jobApplications.updateStatus', '') }}/" + applicationId,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: status
        },
        success: function(response) {
            if (response.success) {
                // Show success message
                alert('Status updated successfully');
                // Reload page to show updated status
                location.reload();
            } else {
                alert('Error updating status: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error updating status. Please try again.');
        }
    });
}
</script>

<style>
.timeline {
    position: relative;
    padding: 1rem 0;
}

.timeline-item {
    position: relative;
    padding-left: 2.5rem;
    padding-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.timeline-content {
    padding-left: 0.5rem;
}

.status-badge {
    font-size: 1rem;
}

.card-title {
    color: #333;
    margin-bottom: 1rem;
}
</style>
@endsection 