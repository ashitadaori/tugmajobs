@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Pending Jobs</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Posted By</th>
                            <th>Posted Date</th>
                            <th>Applications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingJobs as $job)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $job->title }}</span>
                                    <small class="text-muted">{{ $job->location }}</small>
                                </div>
                            </td>
                            <td>{{ $job->employer->company_name ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $job->employer->user->name }}</span>
                                    <small class="text-muted">{{ $job->employer->user->email }}</small>
                                </div>
                            </td>
                            <td>{{ $job->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $job->applications_count ?? 0 }} Applications
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.jobs.show', $job) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <form action="{{ route('admin.jobs.approve', $job) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn btn-sm btn-success"
                                                title="Approve Job">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <button type="button" 
                                            class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $job->id }}"
                                            title="Reject Job">
                                        <i class="fas fa-times"></i> Reject
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $job->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Job: {{ $job->title }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.jobs.reject', $job) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Rejection Reason</label>
                                                            <textarea name="rejection_reason" 
                                                                      class="form-control" 
                                                                      rows="3" 
                                                                      required
                                                                      placeholder="Please provide a reason for rejecting this job posting..."></textarea>
                                                            <div class="form-text">
                                                                This reason will be sent to the employer. Please be clear and professional.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject Job</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No pending jobs found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $pendingJobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
