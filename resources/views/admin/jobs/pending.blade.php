@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Jobs</h3>
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
                                    <th>Posted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingJobs as $job)
                                    <tr>
                                        <td>{{ $job->title }}</td>
                                        <td>{{ $job->employer->name }}</td>
                                        <td>{{ $job->category->name }}</td>
                                        <td>{{ $job->jobType->name }}</td>
                                        <td>{{ $job->created_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('admin.jobs.show', $job) }}" class="btn btn-info btn-sm">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No pending jobs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pendingJobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
