@forelse($users as $user)
    <tr>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
            @php
                $roleBadgeClass = match ($user->role) {
                    'jobseeker' => 'bg-info text-white',
                    'employer' => 'bg-primary',
                    'admin' => 'bg-dark',
                    default => 'bg-secondary'
                };
            @endphp
            <span class="badge {{ $roleBadgeClass }}">
                {{ ucfirst($user->role) }}
            </span>
        </td>
        <td>
            @php
                $kycStatus = $user->kyc_status;
            @endphp
            @if($kycStatus && $kycStatus !== 'not_started')
                @php
                    $kycStatusColor = match ($kycStatus) {
                        'verified' => 'success',
                        'in_progress' => 'warning text-dark',
                        'rejected' => 'danger',
                        default => 'secondary'
                    };
                @endphp
                <span class="badge bg-{{ $kycStatusColor }}">
                    {{ ucfirst(str_replace('_', ' ', $kycStatus)) }}
                </span>
            @endif
        </td>
        <td>{{ $user->created_at->format('M d, Y') }}</td>
        <td>
            <div class="btn-group" role="group">
                {{-- Resume Download for Job Seekers --}}
                @if($user->role === 'jobseeker' && $user->jobSeekerProfile && $user->jobSeekerProfile->resume_file)
                    <a href="{{ asset('storage/resumes/' . $user->jobSeekerProfile->resume_file) }}"
                        class="btn btn-sm btn-outline-danger" target="_blank" title="Download Resume">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                @endif

                {{-- Edit Button --}}
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary"
                    title="Edit User">
                    <i class="fas fa-edit"></i>
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