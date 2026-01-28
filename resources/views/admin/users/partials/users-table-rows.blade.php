@forelse($users as $user)
    <tr>
        <td class="fw-medium">{{ $user->name }}</td>
        <td class="text-muted">{{ $user->email }}</td>
        <td>
            @php
                $roleBadgeClass = match ($user->role) {
                    'jobseeker' => 'badge-role-jobseeker',
                    'employer' => 'badge-role-employer',
                    'admin' => 'badge-role-admin',
                    default => 'bg-secondary'
                };
            @endphp
            <span class="badge {{ $roleBadgeClass }}">
                {{ $user->role === 'jobseeker' ? 'Jobseeker' : ucfirst($user->role) }}
            </span>
        </td>
        <td>
            @php
                $kycStatus = $user->kyc_status;
            @endphp
            @if($kycStatus && $kycStatus !== 'not_started')
                @php
                    $kycLabel = match ($kycStatus) {
                        'verified' => 'Verified',
                        'in_progress' => 'Pending',
                        'rejected' => 'Rejected',
                        default => ucfirst($kycStatus)
                    };
                    $kycBadgeClass = match ($kycStatus) {
                        'verified' => 'badge-kyc-verified',
                        'in_progress' => 'badge-kyc-pending',
                        'rejected' => 'badge-kyc-rejected',
                        default => 'bg-secondary'
                    };
                @endphp
                <span class="badge {{ $kycBadgeClass }}">{{ $kycLabel }}</span>
            @else
                <span class="text-muted small">-</span>
            @endif
        </td>
        <td class="text-muted">{{ $user->created_at->format('M d, Y') }}</td>
        <td>
            <div class="d-flex gap-1">
                {{-- Resume Download for Job Seekers --}}
                @if($user->role === 'jobseeker' && $user->jobSeekerProfile && $user->jobSeekerProfile->resume_file)
                    <a href="{{ asset('storage/resumes/' . $user->jobSeekerProfile->resume_file) }}"
                        class="btn btn-sm btn-outline-danger rounded-2" target="_blank" title="Download Resume">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                @endif

                {{-- Edit Button --}}
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary rounded-2"
                    title="Edit User">
                    <i class="bi bi-pencil-square"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-4">
            <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
            <p class="text-muted mt-2 mb-0">No users found</p>
            @if(request()->has('q') || request()->has('role') || request()->has('kyc_status'))
                <p class="text-muted small">Try adjusting your search or filter criteria</p>
            @endif
        </td>
    </tr>
@endforelse