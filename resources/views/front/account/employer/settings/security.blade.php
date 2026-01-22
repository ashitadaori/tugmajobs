@extends('layouts.employer')

@section('page_title', 'Security Settings')

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Main Column -->
            <div class="col-lg-8">
                <!-- Change Password -->
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="ep-card-title">
                            <i class="fas fa-lock"></i> Change Password
                        </h5>
                    </div>
                    <div class="ep-card-body">
                        <form method="POST" action="{{ route('employer.settings.password.update') }}">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="ep-form-group">
                                        <label class="ep-form-label required">Current Password</label>
                                        <input type="password" name="current_password"
                                            class="ep-form-input @error('current_password') is-invalid @enderror" required
                                            placeholder="Enter current password">
                                        @error('current_password')
                                            <div class="ep-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6"></div>

                                <div class="col-md-6">
                                    <div class="ep-form-group">
                                        <label class="ep-form-label required">New Password</label>
                                        <input type="password" name="new_password"
                                            class="ep-form-input @error('new_password') is-invalid @enderror" required
                                            placeholder="Enter new password">
                                        @error('new_password')
                                            <div class="ep-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="ep-form-group">
                                        <label class="ep-form-label required">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" class="ep-form-input"
                                            required placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="ep-btn ep-btn-primary">
                                    <i class="fas fa-save"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="ep-card-title">
                            <i class="fas fa-shield-alt"></i> Two-Factor Authentication
                        </h5>
                        @if(auth()->user()->two_factor_enabled ?? false)
                            <span class="ep-badge ep-badge-success">Enabled</span>
                        @else
                            <span class="ep-badge ep-badge-warning">Disabled</span>
                        @endif
                    </div>
                    <div class="ep-card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('recovery_codes'))
                            <div class="alert alert-warning" role="alert">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Save Your Recovery Codes</h6>
                                <p class="small mb-2">Store these codes in a safe place. Each code can only be used once.</p>
                                <div class="bg-light p-3 rounded mb-2">
                                    @foreach(session('recovery_codes') as $code)
                                        <code class="d-block">{{ $code }}</code>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-dark" onclick="copyRecoveryCodes()">
                                    <i class="fas fa-copy"></i> Copy Codes
                                </button>
                            </div>
                        @endif

                        <div class="d-flex align-items-center gap-4">
                            <div class="d-none d-md-block">
                                <div class="ep-stat-icon"
                                    style="background: var(--ep-primary-50); color: var(--ep-primary); width: 64px; height: 64px; font-size: 1.5rem;">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                @if(auth()->user()->two_factor_enabled ?? false)
                                    <h6 class="mb-2">Your account is protected with 2FA</h6>
                                    <p class="text-muted mb-0">Two-factor authentication using Google Authenticator is currently enabled.
                                        You'll need to enter a code from your authenticator app when you log in.</p>
                                @else
                                    <h6 class="mb-2">Add an extra layer of security</h6>
                                    <p class="text-muted mb-0">Protect your account by enabling two-factor authentication using
                                        Google Authenticator or any compatible authenticator app.</p>
                                @endif
                            </div>
                            <div class="ms-auto">
                                @if(auth()->user()->two_factor_enabled ?? false)
                                    <button type="button" class="ep-btn ep-btn-outline ep-btn-danger" data-bs-toggle="modal" data-bs-target="#disable2faModal">
                                        Disable 2FA
                                    </button>
                                @else
                                    <a href="{{ route('2fa.setup') }}" class="ep-btn ep-btn-primary">
                                        <i class="fas fa-shield-alt me-1"></i> Enable 2FA
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if(auth()->user()->two_factor_enabled ?? false)
                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Recovery Codes</h6>
                                    <p class="text-muted small mb-0">Recovery codes can be used to access your account if you lose your authenticator device.</p>
                                </div>
                                <div>
                                    <button type="button" class="ep-btn ep-btn-outline ep-btn-sm" data-bs-toggle="modal" data-bs-target="#viewRecoveryCodesModal">
                                        <i class="fas fa-eye"></i> View Codes
                                    </button>
                                    <button type="button" class="ep-btn ep-btn-outline ep-btn-sm" data-bs-toggle="modal" data-bs-target="#regenerateCodesModal">
                                        <i class="fas fa-sync"></i> Regenerate
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- KYC Verification -->
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="ep-card-title">
                            <i class="fas fa-user-check"></i> KYC Verification
                        </h5>
                        @if(auth()->user()->kyc_status === 'verified')
                            <span class="ep-badge ep-badge-success">Verified</span>
                        @elseif(auth()->user()->kyc_status === 'in_progress')
                            <span class="ep-badge ep-badge-warning">In Progress</span>
                        @elseif(auth()->user()->kyc_status === 'failed')
                            <span class="ep-badge ep-badge-danger">Failed</span>
                        @else
                            <span class="ep-badge ep-badge-gray">Pending</span>
                        @endif
                    </div>
                    <div class="ep-card-body">
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-none d-md-block">
                                <div class="ep-stat-icon"
                                    style="background: var(--ep-info-bg); color: var(--ep-info); width: 64px; height: 64px; font-size: 1.5rem;">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-2">Identity Verification</h6>
                                <p class="text-muted mb-0">
                                    Current Status:
                                    <span class="fw-semibold">
                                        @if(auth()->user()->kyc_status === 'verified')
                                            Verified
                                        @elseif(auth()->user()->kyc_status === 'in_progress')
                                            In Review
                                        @elseif(auth()->user()->kyc_status === 'failed')
                                            Verification Failed
                                        @else
                                            Not Verified
                                        @endif
                                    </span>
                                </p>
                                <p class="text-muted small mt-1">Resetting verification will delete all submitted documents.
                                </p>
                            </div>
                            <div class="ms-auto">
                                <button type="button" class="ep-btn ep-btn-outline" data-bs-toggle="modal"
                                    data-bs-target="#resetKycModal">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone (Deactivate Account) -->
                <div class="ep-card" style="border-color: var(--ep-danger-light);">
                    <div class="ep-card-header"
                        style="background: var(--ep-danger-bg); border-bottom-color: var(--ep-danger-light);">
                        <h5 class="ep-card-title" style="color: var(--ep-danger);">
                            <i class="fas fa-exclamation-triangle"></i> Deactivate Account
                        </h5>
                    </div>
                    <div class="ep-card-body">
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-none d-md-block">
                                <div class="ep-stat-icon"
                                    style="background: var(--ep-danger-bg); color: var(--ep-danger); width: 64px; height: 64px; font-size: 1.5rem;">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-danger">Temporarily deactivate your account</h6>
                                <p class="text-muted mb-0">Your profile and job postings will be hidden from job seekers.
                                    You can reactivate your account at any time by logging in.</p>
                            </div>
                            <div class="ms-auto">
                                <button type="button" class="ep-btn ep-btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deactivateAccountModal">
                                    Deactivate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <!-- Security Tips Card -->
                <div class="ep-card bg-primary text-white"
                    style="background: linear-gradient(135deg, var(--ep-primary) 0%, var(--ep-primary-dark) 100%);">
                    <div class="ep-card-body">
                        <h5 class="mb-3"><i class="fas fa-user-shield me-2"></i>Security Tips</h5>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3" style="opacity: 0.9;">
                            <li class="d-flex gap-2">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span class="small">Use a strong, unique password for your account.</span>
                            </li>
                            <li class="d-flex gap-2">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span class="small">Enable 2FA with Google Authenticator for extra security.</span>
                            </li>
                            <li class="d-flex gap-2">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span class="small">Save your recovery codes in a safe place.</span>
                            </li>
                            <li class="d-flex gap-2">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span class="small">Complete KYC to verify your business identity.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Disable 2FA Modal -->
    <div class="modal fade" id="disable2faModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: var(--ep-radius-xl); border: none; box-shadow: var(--ep-shadow-xl);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Disable Two-Factor Authentication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('2fa.disable') }}" id="disable2faForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex gap-3 align-items-start" role="alert">
                            <i class="fas fa-exclamation-triangle mt-1"></i>
                            <div>
                                <strong>Warning:</strong> Disabling 2FA will make your account less secure.
                                You will only need your password to log in.
                            </div>
                        </div>
                        <div class="ep-form-group mb-0">
                            <label class="ep-form-label">Enter your password to confirm:</label>
                            <input type="password" name="password" class="ep-form-input" required placeholder="Your password">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ep-btn ep-btn-danger">Disable 2FA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Recovery Codes Modal -->
    <div class="modal fade" id="viewRecoveryCodesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: var(--ep-radius-xl); border: none; box-shadow: var(--ep-shadow-xl);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">View Recovery Codes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Enter your password to view your recovery codes.</p>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Password</label>
                        <input type="password" id="viewCodesPassword" class="ep-form-input" placeholder="Your password">
                    </div>
                    <div id="recoveryCodesContainer" style="display: none;">
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i> Each code can only be used once.
                        </div>
                        <div class="bg-light p-3 rounded" id="recoveryCodesList">
                            <!-- Codes will be inserted here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="ep-btn ep-btn-primary" id="viewCodesBtn" onclick="viewRecoveryCodes()">
                        <i class="fas fa-eye"></i> View Codes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Regenerate Recovery Codes Modal -->
    <div class="modal fade" id="regenerateCodesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: var(--ep-radius-xl); border: none; box-shadow: var(--ep-shadow-xl);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Regenerate Recovery Codes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('2fa.regenerate-recovery-codes') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex gap-3 align-items-start" role="alert">
                            <i class="fas fa-exclamation-triangle mt-1"></i>
                            <div>
                                <strong>Warning:</strong> This will invalidate all your existing recovery codes.
                                Make sure to save the new codes.
                            </div>
                        </div>
                        <div class="ep-form-group mb-0">
                            <label class="ep-form-label">Enter your password to confirm:</label>
                            <input type="password" name="password" class="ep-form-input" required placeholder="Your password">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ep-btn ep-btn-primary">
                            <i class="fas fa-sync"></i> Regenerate Codes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Deactivate Account Modal -->
    <div class="modal fade" id="deactivateAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content"
                style="border-radius: var(--ep-radius-xl); border: none; box-shadow: var(--ep-shadow-xl);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Deactivate Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('employer.settings.account.deactivate') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex gap-3 align-items-start" role="alert">
                            <i class="fas fa-exclamation-triangle mt-1"></i>
                            <div>
                                <strong>Warning:</strong> Your account will be temporarily deactivated. Your profile and job
                                postings will be hidden. You can reactivate by logging in again.
                            </div>
                        </div>
                        <div class="ep-form-group mb-0">
                            <label class="ep-form-label">Enter your password to confirm:</label>
                            <input type="password" name="password" class="ep-form-input" required
                                placeholder="Your password">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ep-btn ep-btn-danger">Deactivate Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset KYC Modal -->
    <div class="modal fade" id="resetKycModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content"
                style="border-radius: var(--ep-radius-xl); border: none; box-shadow: var(--ep-shadow-xl);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Reset KYC Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('employer.settings.kyc.reset') }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-danger d-flex gap-3 align-items-start" role="alert">
                            <i class="fas fa-exclamation-circle mt-1"></i>
                            <div>
                                <strong>This action cannot be undone!</strong>
                                <ul class="mb-0 mt-2 ps-3 small">
                                    <li>Delete all your KYC verification records</li>
                                    <li>Delete all submitted documents</li>
                                    <li>Reset status to "Pending"</li>
                                </ul>
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="confirmReset" required>
                            <label class="form-check-label small text-muted" for="confirmReset">
                                I understand that this action is permanent.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="ep-btn ep-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ep-btn ep-btn-danger">
                            <i class="fas fa-trash-alt"></i> Reset KYC
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function copyRecoveryCodes() {
    const codes = @json(session('recovery_codes', []));
    const codesText = codes.join('\n');
    navigator.clipboard.writeText(codesText).then(() => {
        alert('Recovery codes copied to clipboard!');
    });
}

function viewRecoveryCodes() {
    const password = document.getElementById('viewCodesPassword').value;
    const btn = document.getElementById('viewCodesBtn');
    const originalText = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    btn.disabled = true;

    fetch('{{ route("2fa.show-recovery-codes") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const container = document.getElementById('recoveryCodesContainer');
            const list = document.getElementById('recoveryCodesList');
            list.innerHTML = data.recovery_codes.map(code => `<code class="d-block">${code}</code>`).join('');
            container.style.display = 'block';
            btn.style.display = 'none';
            document.getElementById('viewCodesPassword').closest('.ep-form-group').style.display = 'none';
        } else {
            alert(data.message || 'Incorrect password.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Reset view codes modal when closed
document.getElementById('viewRecoveryCodesModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('viewCodesPassword').value = '';
    document.getElementById('viewCodesPassword').closest('.ep-form-group').style.display = 'block';
    document.getElementById('recoveryCodesContainer').style.display = 'none';
    document.getElementById('viewCodesBtn').style.display = 'inline-block';
    document.getElementById('viewCodesBtn').disabled = false;
    document.getElementById('viewCodesBtn').innerHTML = '<i class="fas fa-eye"></i> View Codes';
});
</script>
@endsection
