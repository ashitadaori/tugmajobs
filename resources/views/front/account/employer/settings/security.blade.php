@extends('layouts.employer')

@section('page_title', 'Security Settings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Manage your security settings</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <!-- Change Password -->
            <div class="table-card mb-4">
                <div class="table-header">
                    <h5 class="table-title">Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('employer.settings.password.update') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Two-Factor Authentication -->
            <div class="table-card mb-4">
                <div class="table-header">
                    <h5 class="table-title">Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    @if($employer->two_factor_enabled ?? false)
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-shield-alt me-2"></i>
                            Two-factor authentication is <strong>enabled</strong> for your account.
                        </div>
                        <form method="POST" action="{{ route('employer.settings.2fa.disable') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning">Disable 2FA</button>
                        </form>
                    @else
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Two-factor authentication is <strong>disabled</strong>. Enable it for better security.
                        </div>
                        <form method="POST" action="{{ route('employer.settings.2fa.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-success">Enable 2FA</button>
                        </form>
                    @endif
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="table-card border-warning">
                <div class="table-header bg-warning text-dark">
                    <h5 class="table-title text-dark">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6>Deactivate Account</h6>
                            <p class="text-muted">Temporarily deactivate your account. Your profile and job postings will be hidden from job seekers. You can reactivate your account at any time by logging in.</p>
                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#deactivateAccountModal">Deactivate Account</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Account Modal -->
<div class="modal fade" id="deactivateAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deactivate Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employer.settings.account.deactivate') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <strong>Note:</strong> Your account will be temporarily deactivated. Your profile and job postings will be hidden. You can reactivate by logging in again.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enter your password to confirm:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Deactivate Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
