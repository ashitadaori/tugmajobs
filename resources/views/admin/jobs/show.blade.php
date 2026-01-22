@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Review Job: {{ $job->title }}</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Job Details</h5>
                            <table class="table">
                                <tr>
                                    <th>Title:</th>
                                    <td>{{ $job->title }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $job->category->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>{{ $job->jobType->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $job->location ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Salary Range:</th>
                                    <td>
                                        @if($job->salary_min && $job->salary_max)
                                            ₱{{ number_format($job->salary_min, 2) }} - ₱{{ number_format($job->salary_max, 2) }}
                                        @elseif($job->salary_range)
                                            {{ $job->salary_range }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Posted:</th>
                                    <td>{{ $job->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Employer Details</h5>
                            <table class="table">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $job->employer->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $job->employer->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Company:</th>
                                    <td>
                                        @if($job->employer && $job->employer->company_name)
                                            {{ $job->employer->company_name }}
                                        @elseif($job->company_name)
                                            {{ $job->company_name }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @php
                                            $statusClass = 'secondary';
                                            $statusText = 'Unknown';
                                            if ($job->status == \App\Models\Job::STATUS_PENDING) {
                                                $statusClass = 'warning';
                                                $statusText = 'Pending';
                                            } elseif ($job->status == \App\Models\Job::STATUS_APPROVED) {
                                                $statusClass = 'success';
                                                $statusText = 'Approved';
                                            } elseif ($job->status == \App\Models\Job::STATUS_REJECTED) {
                                                $statusClass = 'danger';
                                                $statusText = 'Rejected';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                                @if($job->vacancies)
                                <tr>
                                    <th>Vacancies:</th>
                                    <td>{{ $job->vacancies }}</td>
                                </tr>
                                @endif
                                @if($job->experience_level)
                                <tr>
                                    <th>Experience Level:</th>
                                    <td>{{ ucfirst($job->experience_level) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Description</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($job->description)) !!}
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Requirements</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($job->requirements)) !!}
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Benefits</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($job->benefits)) !!}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.jobs.pending') }}" class="btn btn-secondary">
                            Back to List
                        </a>
                        <div>
                            @if($job->status == \App\Models\Job::STATUS_PENDING)
                                {{-- Only show Approve/Reject buttons for pending jobs --}}
                                <form action="{{ route('admin.jobs.approve', $job) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i> Approve Job
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="fas fa-times me-1"></i> Reject Job
                                </button>
                            @elseif($job->status == \App\Models\Job::STATUS_APPROVED)
                                {{-- Show approved status indicator --}}
                                <span class="btn btn-success disabled">
                                    <i class="fas fa-check-circle me-1"></i> Job Approved
                                </span>
                            @elseif($job->status == \App\Models\Job::STATUS_REJECTED)
                                {{-- Show rejected status indicator --}}
                                <span class="btn btn-danger disabled">
                                    <i class="fas fa-times-circle me-1"></i> Job Rejected
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($job->status == \App\Models\Job::STATUS_PENDING)
<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.jobs.reject', $job) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason</label>
                        <textarea id="rejection_reason"
                                name="rejection_reason"
                                class="form-control @error('rejection_reason') is-invalid @enderror"
                                rows="3"
                                required
                                placeholder="Please provide a reason for rejecting this job posting..."></textarea>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            This reason will be sent to the employer.
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
@endif
@endsection 