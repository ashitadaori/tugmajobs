<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Title</th>
                <th>Company</th>
                <th>Status</th>
                <th>Posted Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobs as $job)
            <tr>
                <td>
                    <div class="d-flex flex-column">
                        <span class="fw-bold">{{ $job->title }}</span>
                        <small class="text-muted">{{ $job->location }}</small>
                    </div>
                </td>
                <td>{{ optional($job->employer)->company_name ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-{{ $job->status === 'active' ? 'success' : ($job->status === 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($job->status) }}
                    </span>
                </td>
                <td>{{ $job->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="btn-group">
                        <a href="{{ route('admin.jobs.show', $job) }}" 
                           class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        
                        @if($job->status === 'pending')
                            <form action="{{ route('admin.jobs.approve', $job) }}" 
                                  method="POST" 
                                  class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="btn btn-success btn-sm ms-1"
                                        onclick="return confirm('Are you sure you want to approve this job?')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>

                            <button type="button" 
                                    class="btn btn-danger btn-sm ms-1"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#rejectModal{{ $job->id }}">
                                <i class="fas fa-times"></i> Reject
                            </button>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $job->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Reject Job: {{ $job->title }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.jobs.reject', $job) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Please provide a detailed reason for rejecting this job. This information will be sent to the employer.
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label required">Rejection Reason</label>
                                                    <textarea name="rejection_reason" 
                                                              class="form-control @error('rejection_reason') is-invalid @enderror" 
                                                              rows="4" 
                                                              required
                                                              placeholder="Please explain why this job posting is being rejected..."></textarea>
                                                    @error('rejection_reason')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">
                                                        Be clear and professional. The employer will use this feedback to make necessary corrections.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times me-2"></i>Cancel
                                                </button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times-circle me-2"></i>Reject Job
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No jobs found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
