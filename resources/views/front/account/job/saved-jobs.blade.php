@extends('layouts.jobseeker')

@section('page-title', 'Saved Jobs')

@section('jobseeker-content')
<div class="card border-0 shadow mb-4">
    <div class="card-body p-4">
                        <h3 class="fs-4 mb-1">Saved Jobs</h3>
                        <p class="mb-4 text-muted">Jobs you've saved for later</p>
                        
                        @if($savedJobs->count() > 0)
                            <div class="row">
                                @foreach($savedJobs as $job)
                                    <div class="col-md-12 mb-4">
                                        <div class="card job-card border">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title mb-2">
                                                            <a href="{{ route('jobDetail', $job->id) }}" class="text-decoration-none">
                                                                {{ $job->title }}
                                                            </a>
                                                        </h5>
                                                        <p class="text-muted mb-2">
                                                            <i class="fas fa-building me-2"></i>{{ $job->company ? $job->company->company_name : 'N/A' }}
                                                        </p>
                                                        <p class="text-muted mb-2">
                                                            <i class="fas fa-map-marker-alt me-2"></i>{{ $job->location }}
                                                        </p>
                                                        @if($job->jobType)
                                                            <span class="badge bg-primary me-2">{{ $job->jobType->name }}</span>
                                                        @endif
                                                        @if($job->salary)
                                                            <span class="text-success fw-bold">${{ number_format($job->salary) }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex flex-column align-items-end">
                                                        <small class="text-muted mb-2">
                                                            Saved {{ $job->pivot->created_at->diffForHumans() }}
                                                        </small>
                                                        <div class="btn-group">
                                                            <a href="{{ route('jobDetail', $job->id) }}" class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-eye me-1"></i>View
                                                            </a>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSavedJob({{ $job->id }})">
                                                                <i class="fas fa-trash me-1"></i>Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($job->description)
                                                    <p class="card-text mt-3">
                                                        {{ Str::limit(strip_tags($job->description), 150) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $savedJobs->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No Saved Jobs</h4>
                                <p class="text-muted">You haven't saved any jobs yet. Start browsing jobs and save the ones you're interested in!</p>
                                <a href="{{ route('jobs') }}" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Browse Jobs
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
@endsection

@section('customJs')
<script type="text/javascript">
function removeSavedJob(jobId){
    if(confirm("Are you sure you want to remove this job?")){
        $.ajax({
            url: '{{ route("account.removeSavedJob") }}',
            type: 'POST',
            data: { job_id: jobId, _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(response) {
                if(response.status == true) {
                    window.location.href="{{ route('account.saved-jobs.index') }}";
                }
            }
        });
    }
}
</script>
@endsection


