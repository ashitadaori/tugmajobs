@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Jobs</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Employer</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Posted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobs as $job)
                                    <tr>
                                        <td>{{ $job->title }}</td>
                                        <td>{{ $job->employer->name }}</td>
                                        <td>{{ $job->category->name }}</td>
                                        <td>{{ $job->jobType->name }}</td>
                                        <td>
                                            @if($job->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($job->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($job->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($job->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $job->created_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('admin.jobs.show', $job) }}" class="btn btn-info btn-sm">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No jobs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 