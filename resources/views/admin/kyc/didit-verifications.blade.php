@extends('layouts.admin')

@section('page_title', 'KYC Verifications')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <i class="bi bi-card-checklist me-2"></i>
            DiDit KYC Verifications
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kyc.didit-verifications') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search User</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Name or Email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.kyc.didit-verifications') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Status</th>
                            <th>Document Info</th>
                            <th>Personal Info</th>
                            <th>Verification Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                // Get verification data from either kyc_verifications or kyc_data table
                                $verification = $user->kycVerifications->first();
                                $kycData = $user->kycData->first();
                                
                                // Use verification data if available, otherwise fall back to kyc_data or user status
                                $status = $verification->status ?? $kycData->status ?? $user->kyc_status ?? 'pending';
                                $statusColor = match($status) {
                                    'verified' => 'success',
                                    'failed' => 'danger',
                                    'expired' => 'warning',
                                    'in_progress' => 'info',
                                    default => 'secondary'
                                };
                                
                                // Merge data from both sources for display
                                $displayData = (object) [
                                    'status' => $status,
                                    'session_id' => $verification->session_id ?? $kycData->session_id ?? null,
                                    'document_type' => $verification->document_type ?? $kycData->document_type ?? null,
                                    'document_number' => $verification->document_number ?? $kycData->document_number ?? null,
                                    'firstname' => $verification->firstname ?? $kycData->first_name ?? null,
                                    'lastname' => $verification->lastname ?? $kycData->last_name ?? null,
                                    'date_of_birth' => $verification->date_of_birth ?? $kycData->date_of_birth ?? null,
                                    'nationality' => $verification->nationality ?? $kycData->nationality ?? null,
                                    'created_at' => $verification->created_at ?? $kycData->created_at ?? null,
                                    'verified_at' => $verification->verified_at ?? $kycData->verified_at ?? $user->kyc_verified_at ?? null,
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            @if($user->profile_image)
                                                <img src="{{ $user->profile_image }}" 
                                                     class="rounded-circle" 
                                                     width="40" height="40" 
                                                     alt="{{ $user->name }}">
                                            @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; font-size: 18px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                            <span class="badge bg-{{ $user->role === 'employer' ? 'info' : 'primary' }} small">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst($displayData->status) }}
                                    </span>
                                    @if($displayData->session_id)
                                        <div class="text-muted small mt-1">
                                            Session: {{ substr($displayData->session_id, 0, 8) }}...
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($displayData->document_type || $displayData->document_number)
                                        <div class="small">
                                            @if($displayData->document_type)
                                                <div><strong>Type:</strong> {{ $displayData->document_type }}</div>
                                            @endif
                                            @if($displayData->document_number)
                                                <div><strong>Number:</strong> {{ substr($displayData->document_number, 0, -4) }}****</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not available</span>
                                    @endif
                                </td>
                                <td>
                                    @if($displayData->firstname || $displayData->lastname)
                                        <div class="small">
                                            <div><strong>Name:</strong> {{ trim(($displayData->firstname ?? '') . ' ' . ($displayData->lastname ?? '')) }}</div>
                                            @if($displayData->date_of_birth)
                                                <div><strong>DOB:</strong> {{ 
                                                    is_string($displayData->date_of_birth) 
                                                        ? \Carbon\Carbon::parse($displayData->date_of_birth)->format('M j, Y')
                                                        : $displayData->date_of_birth->format('M j, Y') 
                                                }}</div>
                                            @endif
                                            @if($displayData->nationality)
                                                <div><strong>Nationality:</strong> {{ $displayData->nationality }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not available</span>
                                    @endif
                                </td>
                                <td>
                                    @if($displayData->created_at)
                                        <div class="small">
                                            <div><strong>Created:</strong> {{ 
                                                is_string($displayData->created_at) 
                                                    ? \Carbon\Carbon::parse($displayData->created_at)->format('M j, Y g:i A')
                                                    : $displayData->created_at->format('M j, Y g:i A') 
                                            }}</div>
                                            @if($displayData->verified_at)
                                                <div><strong>Verified:</strong> {{ 
                                                    is_string($displayData->verified_at) 
                                                        ? \Carbon\Carbon::parse($displayData->verified_at)->format('M j, Y g:i A')
                                                        : $displayData->verified_at->format('M j, Y g:i A') 
                                                }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                    <div class="d-flex gap-2">
                        <!-- View Details Button -->
                        <a href="{{ route('admin.kyc.show-didit-verification', $user) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        <!-- Refresh Button for Dynamic Data -->
                        <button type="button" 
                                class="btn btn-sm btn-outline-info refresh-verification" 
                                data-user-id="{{ $user->id }}"
                                title="Refresh Verification Data">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                                        
                                        @if($displayData->status !== 'verified')
                                            <!-- Approve Button -->
                                            <form action="{{ route('admin.kyc.approve-didit-verification', $user) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to approve this KYC verification?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(!in_array($displayData->status, ['failed', 'verified']))
                                            <!-- Reject Button -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal{{ $user->id }}"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            @if(!in_array($displayData->status, ['failed', 'verified']))
                            <div class="modal fade" id="rejectModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject KYC Verification</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.kyc.reject-didit-verification', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-body">
                                                <p><strong>User:</strong> {{ $user->name }} ({{ $user->email }})</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Rejection Reason</label>
                                                    <textarea name="rejection_reason" class="form-control" rows="3" required 
                                                              placeholder="Please provide a reason for rejection..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Verification</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p>No KYC verifications found.</p>
                                    @if(request()->hasAny(['status', 'search']))
                                        <a href="{{ route('admin.kyc.didit-verifications') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-times me-1"></i>Clear Filters
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle refresh verification button clicks
    const refreshButtons = document.querySelectorAll('.refresh-verification');
    
    refreshButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const originalIcon = this.querySelector('i');
            const originalText = originalIcon.className;
            
            // Show loading state
            this.disabled = true;
            originalIcon.className = 'fas fa-spinner fa-spin';
            this.setAttribute('title', 'Refreshing...');
            
            // Make AJAX request to refresh verification data
            fetch(`/admin/kyc/refresh-verification/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('success', 'Verification data refreshed successfully!');
                    
                    // Reload the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to refresh verification data');
                }
            })
            .catch(error => {
                console.error('Refresh error:', error);
                showNotification('error', 'Failed to refresh verification data: ' + error.message);
                
                // Reset button state
                this.disabled = false;
                originalIcon.className = originalText;
                this.setAttribute('title', 'Refresh Verification Data');
            });
        });
    });
    
    // Utility function to show notifications
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
});
</script>
@endsection

@section('styles')
<style>
.user-avatar img,
.user-avatar div {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-top: none;
}

.badge {
    font-size: 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endsection
