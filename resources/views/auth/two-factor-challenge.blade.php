@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h4>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div id="code-form">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                            </div>
                            <p class="text-muted">
                                Open your authenticator app and enter the 6-digit verification code.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('two-factor.verify') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="code" class="form-label">Verification Code</label>
                                <input type="text"
                                       class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                       id="code"
                                       name="code"
                                       maxlength="6"
                                       pattern="\d{6}"
                                       inputmode="numeric"
                                       placeholder="000000"
                                       autofocus
                                       autocomplete="one-time-code">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-check me-2"></i>Verify Code
                            </button>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleRecoveryForm()">
                                <i class="fas fa-key me-1"></i>Use Recovery Code Instead
                            </button>
                        </div>
                    </div>

                    <div id="recovery-form" style="display: none;">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-key fa-3x text-warning"></i>
                            </div>
                            <p class="text-muted">
                                Enter one of your recovery codes to access your account.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('two-factor.verify-recovery') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="recovery_code" class="form-label">Recovery Code</label>
                                <input type="text"
                                       class="form-control @error('recovery_code') is-invalid @enderror"
                                       id="recovery_code"
                                       name="recovery_code"
                                       placeholder="XXXXX-XXXXX">
                                @error('recovery_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-check me-2"></i>Verify Recovery Code
                            </button>
                        </form>

                        <div class="text-center">
                            <button type="button" class="btn btn-link text-decoration-none" onclick="toggleRecoveryForm()">
                                <i class="fas fa-arrow-left me-1"></i>Back to Verification Code
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-muted">
                            <i class="fas fa-sign-out-alt me-1"></i>Cancel and Return to Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Help Text -->
            <div class="card mt-3 border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-question-circle me-1"></i> Need Help?</h6>
                    <ul class="small text-muted mb-0">
                        <li>Open your authenticator app (Google Authenticator, Microsoft Authenticator, etc.)</li>
                        <li>Find the entry for "{{ config('app.name', 'TugmaJobs') }}"</li>
                        <li>Enter the 6-digit code shown in the app</li>
                        <li>If you've lost access to your authenticator, use a recovery code</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRecoveryForm() {
    const codeForm = document.getElementById('code-form');
    const recoveryForm = document.getElementById('recovery-form');

    if (codeForm.style.display === 'none') {
        codeForm.style.display = 'block';
        recoveryForm.style.display = 'none';
    } else {
        codeForm.style.display = 'none';
        recoveryForm.style.display = 'block';
    }
}

// Auto-submit when 6 digits are entered
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length === 6) {
        // Optional: auto-submit after a brief delay
        // setTimeout(() => this.form.submit(), 300);
    }
});
</script>
@endsection
