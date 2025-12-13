<!-- Recent Jobs -->
<div class="card mt-4">
    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Jobs</h5>
            <a href="{{ route('employer.jobs.index') }}" class="btn btn-link p-0">View All</a>
        </div>
    </div>
    <div class="card-body px-4">
        @if(isset($recentJobs) && $recentJobs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentJobs as $job)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ $job->title }}</h6>
                                        <small class="text-muted">{{ $job->location }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $job->applications_count ?? 0 }}</td>
                            <td>
                                <span class="badge bg-{{ $job->status === 'active' ? 'success' : 'warning' }}-subtle text-{{ $job->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($job->status) }}
                                </span>
                            </td>
                            <td>{{ $job->created_at->diffForHumans() }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-link dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('employer.jobs.show', $job->id) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                        <li><a class="dropdown-item" href="{{ route('employer.jobs.edit', $job->id) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                        @if($job->status === 'active')
                                        <li><a class="dropdown-item text-warning" href="#"><i class="bi bi-pause-circle me-2"></i>Pause</a></li>
                                        @else
                                        <li><a class="dropdown-item text-success" href="#"><i class="bi bi-play-circle me-2"></i>Activate</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <img src="{{ asset('images/empty-jobs.svg') }}" alt="No jobs" class="img-fluid mb-3" style="max-width: 200px;">
                <h6>No jobs posted yet</h6>
                <p class="text-muted">Start by posting your first job opening</p>
                @php
                    $canPostJobs = Auth::user()->canPostJobs();
                    $verificationStatus = Auth::user()->getEmployerVerificationStatus();
                @endphp
                @if($canPostJobs)
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Post New Job
                    </a>
                @else
                    <div class="d-flex flex-column align-items-center gap-2">
                        <button class="btn btn-secondary" disabled title="{{ $verificationStatus['message'] }}">
                            <i class="bi bi-lock me-2"></i>Post New Job
                        </button>
                        <small class="text-muted">{{ $verificationStatus['message'] }}</small>
                        @if($verificationStatus['status'] === 'kyc_pending')
                            <button class="btn btn-outline-warning btn-sm" onclick="window.startInlineVerification ? window.startInlineVerification() : alert('KYC verification not available')">
                                <i class="bi bi-shield-check me-1"></i>Complete KYC First
                            </button>
                        @elseif($verificationStatus['status'] === 'documents_pending')
                            <a href="{{ route('employer.documents.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark-text me-1"></i>Submit Documents
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</div> 