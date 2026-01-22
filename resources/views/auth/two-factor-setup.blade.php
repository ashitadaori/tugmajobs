@extends('layouts.employer')

@section('page_title', 'Setup Two-Factor Authentication')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ep-card">
                <div class="ep-card-header">
                    <h5 class="ep-card-title">
                        <i class="fas fa-shield-alt"></i> Setup Two-Factor Authentication
                    </h5>
                </div>
                <div class="ep-card-body">
                    <div class="row">
                        <!-- Step 1: Download App -->
                        <div class="col-md-12 mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="step-number">1</div>
                                <div>
                                    <h6 class="mb-2">Download an Authenticator App</h6>
                                    <p class="text-muted mb-2">
                                        Download and install an authenticator app on your mobile device:
                                    </p>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                                           target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fab fa-google-play me-1"></i> Google Authenticator
                                        </a>
                                        <a href="https://apps.apple.com/app/google-authenticator/id388497605"
                                           target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fab fa-app-store me-1"></i> Google Authenticator (iOS)
                                        </a>
                                        <a href="https://www.microsoft.com/en-us/security/mobile-authenticator-app"
                                           target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fab fa-microsoft me-1"></i> Microsoft Authenticator
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Scan QR Code -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="step-number">2</div>
                                <div>
                                    <h6 class="mb-2">Scan this QR Code</h6>
                                    <p class="text-muted mb-3">
                                        Open your authenticator app and scan this QR code:
                                    </p>
                                    <div class="qr-code-container p-3 bg-white rounded border text-center">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Entry -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="step-number-alt"><i class="fas fa-keyboard"></i></div>
                                <div>
                                    <h6 class="mb-2">Or Enter Code Manually</h6>
                                    <p class="text-muted mb-3">
                                        If you can't scan the QR code, enter this secret key manually in your authenticator app:
                                    </p>
                                    <div class="secret-key-container p-3 bg-light rounded border">
                                        <code class="user-select-all" id="secretKey">{{ $secret }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="copySecret()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-info-circle"></i> Keep this code secret. Do not share it with anyone.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Verify -->
                        <div class="col-md-12">
                            <div class="d-flex align-items-start gap-3">
                                <div class="step-number">3</div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-2">Verify Setup</h6>
                                    <p class="text-muted mb-3">
                                        Enter the 6-digit code from your authenticator app to verify the setup:
                                    </p>
                                    <form id="verifyForm" action="{{ route('2fa.confirm-setup') }}" method="POST">
                                        @csrf
                                        <div class="row align-items-end">
                                            <div class="col-md-6">
                                                <div class="ep-form-group mb-0">
                                                    <label class="ep-form-label">Verification Code</label>
                                                    <input type="text"
                                                           name="code"
                                                           id="verificationCode"
                                                           class="ep-form-input @error('code') is-invalid @enderror"
                                                           placeholder="Enter 6-digit code"
                                                           maxlength="6"
                                                           pattern="[0-9]{6}"
                                                           inputmode="numeric"
                                                           autocomplete="one-time-code"
                                                           required>
                                                    @error('code')
                                                        <div class="ep-form-error">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="submit" class="ep-btn ep-btn-primary" id="verifyBtn">
                                                    <i class="fas fa-check"></i> Verify & Enable 2FA
                                                </button>
                                                <a href="{{ route('employer.settings.security') }}" class="ep-btn ep-btn-secondary ms-2">
                                                    Cancel
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="ep-card mt-4 border-warning">
                <div class="ep-card-body">
                    <div class="d-flex gap-3">
                        <div class="text-warning">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-2">Important Security Information</h6>
                            <ul class="mb-0 text-muted small">
                                <li>After enabling 2FA, you will need to enter a code from your authenticator app every time you log in.</li>
                                <li>Make sure to save your recovery codes in a safe place. You'll need them if you lose access to your authenticator app.</li>
                                <li>Never share your secret key or recovery codes with anyone.</li>
                                <li>If you change your phone, you'll need to set up the authenticator app again.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recovery Codes Modal -->
<div class="modal fade" id="recoveryCodesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--ep-radius-xl); border: none; box-shadow: var(--ep-shadow-xl);">
            <div class="modal-header border-0 bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i> 2FA Enabled Successfully!
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Save these recovery codes!</strong>
                    <p class="mb-0 small mt-1">
                        Store them in a safe place. You'll need them if you lose access to your authenticator app.
                    </p>
                </div>
                <div class="recovery-codes-list bg-light p-3 rounded" id="recoveryCodesList">
                    <!-- Recovery codes will be inserted here -->
                </div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyRecoveryCodes()">
                        <i class="fas fa-copy"></i> Copy All Codes
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadRecoveryCodes()">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0">
                <a href="{{ route('employer.settings.security') }}" class="ep-btn ep-btn-primary w-100">
                    <i class="fas fa-check"></i> I've Saved My Recovery Codes
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.step-number {
    width: 32px;
    height: 32px;
    background: var(--ep-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-number-alt {
    width: 32px;
    height: 32px;
    background: var(--ep-gray-200);
    color: var(--ep-gray-600);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.qr-code-container svg {
    max-width: 200px;
    height: auto;
}

.secret-key-container {
    font-family: monospace;
    word-break: break-all;
}

.recovery-codes-list {
    font-family: monospace;
    font-size: 0.9rem;
}

.recovery-codes-list .code {
    padding: 4px 8px;
    margin: 2px;
    background: white;
    border-radius: 4px;
    display: inline-block;
}
</style>

<script>
function copySecret() {
    const secretKey = document.getElementById('secretKey').textContent;
    navigator.clipboard.writeText(secretKey).then(() => {
        alert('Secret key copied to clipboard!');
    });
}

let recoveryCodes = [];

document.getElementById('verifyForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('verifyBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    btn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            code: document.getElementById('verificationCode').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            recoveryCodes = data.recovery_codes;
            displayRecoveryCodes(recoveryCodes);
            new bootstrap.Modal(document.getElementById('recoveryCodesModal')).show();
        } else {
            alert(data.message || 'Invalid verification code. Please try again.');
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
});

function displayRecoveryCodes(codes) {
    const container = document.getElementById('recoveryCodesList');
    container.innerHTML = codes.map(code => `<span class="code">${code}</span>`).join('');
}

function copyRecoveryCodes() {
    const codesText = recoveryCodes.join('\n');
    navigator.clipboard.writeText(codesText).then(() => {
        alert('Recovery codes copied to clipboard!');
    });
}

function downloadRecoveryCodes() {
    const codesText = 'TugmaJobs Recovery Codes\n' +
                      '========================\n\n' +
                      'Keep these codes safe. Each code can only be used once.\n\n' +
                      recoveryCodes.join('\n') +
                      '\n\nGenerated: ' + new Date().toISOString();

    const blob = new Blob([codesText], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'tugmajobs-recovery-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
