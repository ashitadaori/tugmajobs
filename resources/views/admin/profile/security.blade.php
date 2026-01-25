@extends('layouts.admin')

@section('page_title', 'Security Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.updatePassword') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                           id="current_password" name="current_password" required placeholder="Enter current password">
                                    @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6"></div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required placeholder="Enter new password">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h5>
                    @if(auth()->user()->two_factor_enabled ?? false)
                        <span class="badge bg-success">Enabled</span>
                    @else
                        <span class="badge bg-warning text-dark">Disabled</span>
                    @endif
                </div>
                <div class="card-body">
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
                        <div class="d-none d-md-flex align-items-center justify-content-center"
                             style="width: 64px; height: 64px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 12px;">
                            <i class="fas fa-mobile-alt text-white" style="font-size: 1.5rem;"></i>
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
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#disable2faModal">
                                    Disable 2FA
                                </button>
                            @else
                                <a href="{{ route('2fa.setup') }}" class="btn btn-primary">
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
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#viewRecoveryCodesModal">
                                    <i class="fas fa-eye"></i> View Codes
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#regenerateCodesModal">
                                    <i class="fas fa-sync"></i> Regenerate
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Security Tips Card -->
            <div class="card text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-user-shield me-2"></i>Security Tips</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex gap-2 mb-3">
                            <i class="fas fa-check-circle mt-1"></i>
                            <span class="small">Use a strong, unique password for your account.</span>
                        </li>
                        <li class="d-flex gap-2 mb-3">
                            <i class="fas fa-check-circle mt-1"></i>
                            <span class="small">Enable 2FA with Google Authenticator for extra security.</span>
                        </li>
                        <li class="d-flex gap-2 mb-3">
                            <i class="fas fa-check-circle mt-1"></i>
                            <span class="small">Save your recovery codes in a safe place.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fas fa-check-circle mt-1"></i>
                            <span class="small">Never share your login credentials with anyone.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Back to Profile -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div class="modal fade" id="disable2faModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Disable Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('2fa.disable') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning d-flex gap-3 align-items-start" role="alert">
                        <i class="fas fa-exclamation-triangle mt-1"></i>
                        <div>
                            <strong>Warning:</strong> Disabling 2FA will make your account less secure.
                            You will only need your password to log in.
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Enter your password to confirm:</label>
                        <input type="password" name="password" class="form-control" required placeholder="Your password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Disable 2FA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Recovery Codes Modal -->
<div class="modal fade" id="viewRecoveryCodesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Recovery Codes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Enter your password to view your recovery codes.</p>
                <div class="mb-3" id="viewCodesPasswordGroup">
                    <label class="form-label">Password</label>
                    <input type="password" id="viewCodesPassword" class="form-control" placeholder="Your password">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewCodesBtn" onclick="viewRecoveryCodes()">
                    <i class="fas fa-eye"></i> View Codes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Regenerate Recovery Codes Modal -->
<div class="modal fade" id="regenerateCodesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Regenerate Recovery Codes</h5>
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
                    <div class="mb-0">
                        <label class="form-label">Enter your password to confirm:</label>
                        <input type="password" name="password" class="form-control" required placeholder="Your password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync"></i> Regenerate Codes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
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
            document.getElementById('viewCodesPasswordGroup').style.display = 'none';
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
    document.getElementById('viewCodesPasswordGroup').style.display = 'block';
    document.getElementById('recoveryCodesContainer').style.display = 'none';
    document.getElementById('viewCodesBtn').style.display = 'inline-block';
    document.getElementById('viewCodesBtn').disabled = false;
    document.getElementById('viewCodesBtn').innerHTML = '<i class="fas fa-eye"></i> View Codes';
});
</script>
@endpush
@endsection
