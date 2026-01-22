@forelse($jobs as $job)
    <tr>
        <td>
            <div class="fw-bold">{{ $job->title }}</div>
            @if($job->posted_by_admin ?? false)
                <span class="badge bg-info text-white" style="font-size: 0.7rem;">
                    <i class="bi bi-shield-check"></i> Admin Posted
                </span>
            @endif
        </td>
        <td>{{ $job->employer->name ?? 'N/A' }}</td>
        <td>{{ $job->category->name ?? 'N/A' }}</td>
        <td>{{ $job->jobType->name ?? 'N/A' }}</td>
        <td>
            @if($job->status === 0)
                <span class="badge bg-warning text-dark">Pending</span>
            @elseif($job->status === 1)
                <span class="badge bg-success">Approved</span>
            @elseif($job->status === 2)
                <span class="badge bg-danger">Rejected</span>
            @elseif($job->status === 3)
                <span class="badge bg-secondary">Expired</span>
            @elseif($job->status === 4)
                <span class="badge bg-dark">Closed</span>
            @else
                <span class="badge bg-secondary">{{ $job->status }}</span>
            @endif
        </td>
        <td>
            <small class="text-muted">{{ $job->created_at->format('M d, Y') }}</small>
        </td>
        <td class="text-center">
            <a href="{{ route('admin.jobs.show', $job) }}"
               class="btn btn-sm btn-outline-primary"
               title="View Details">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.jobs.applicants', $job->id) }}"
               class="btn btn-sm btn-outline-success"
               title="View Applicants">
                <i class="bi bi-people"></i> ({{ $job->applications_count ?? 0 }})
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-2 mb-0">No jobs found</p>
            @if(request()->has('q') || request()->has('status'))
                <p class="text-muted small">Try adjusting your search or filter criteria</p>
            @else
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle me-2"></i>Post Your First Job
                </a>
            @endif
        </td>
    </tr>
@endforelse
