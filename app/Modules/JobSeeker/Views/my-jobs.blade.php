@extends('front.layouts.app')

@section('content')
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('front.account.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="dashboard-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">My Jobs</h2>
                        <a href="{{ route('account.job.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Post New Job
                        </a>
                    </div>

                    @include('front.message')

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Job Type</th>
                                    <th>Location</th>
                                    <th>Applications</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($jobs->isNotEmpty())
                                    @foreach($jobs as $job)
                                    <tr>
                                        <td>{{ $job->title }}</td>
                                        <td>{{ $job->jobType->name }}</td>
                                        <td>{{ $job->location }}</td>
                                        <td>{{ $job->applications->count() }}</td>
                                        <td>
                                            @if($job->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('account.job.edit', $job->id) }}" class="btn btn-sm btn-primary me-2">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('jobDetail', $job->id) }}" class="btn btn-sm btn-info me-2">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button onclick="deleteJob({{ $job->id }})" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">No jobs found.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('customJs')
<script>
function deleteJob(jobId) {
    if (confirm("Are you sure you want to delete this job?")) {
        $.ajax({
            url: "{{ route('account.job.delete') }}",
            type: 'post',
            data: {
                job_id: jobId,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    window.location.href = "{{ route('account.job.my-jobs') }}";
                }
            }
        });
    }
}
</script>
@endsection
