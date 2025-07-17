@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if($isSandbox)
            <div class="alert alert-info mb-4">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Sandbox Mode Active
                </h5>
                <p class="mb-0">
                    You are using Persona's sandbox environment. Test the verification flow using these sample documents:
                </p>
                <ul class="mt-2 mb-0">
                    <li>Driver's License: Use any US state's sample license</li>
                    <li>Passport: Use any country's sample passport</li>
                    <li>Test Mode: All verifications will be automatically approved</li>
                </ul>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h3 class="text-center mb-4">Identity Verification</h3>
                    <div id="persona-status" class="alert d-none mb-4"></div>
                    <div id="persona-iframe"></div>
                    
                    <!-- Debug information (only visible in sandbox) -->
                    @if($isSandbox)
                    <div class="mt-4">
                        <div id="debug-info" class="alert alert-secondary">
                            <h6>Debug Information:</h6>
                            <pre id="debug-content" style="white-space: pre-wrap;"></pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Load Persona SDK with async and defer -->
<script async defer src="https://cdn.withpersona.com/dist/persona-v4.js" onload="onPersonaLoad()"></script>
<script>
    function debugLog(message, data = null) {
        const debugContent = document.getElementById('debug-content');
        if (debugContent) {
            const timestamp = new Date().toISOString();
            const logMessage = data 
                ? `${timestamp}: ${message}\n${JSON.stringify(data, null, 2)}\n\n`
                : `${timestamp}: ${message}\n\n`;
            debugContent.textContent += logMessage;
        }
        console.log(message, data || '');
    }

    function showStatus(message, type = 'info') {
        debugLog(`Status Update: ${message} (${type})`);
        const statusDiv = document.getElementById('persona-status');
        statusDiv.className = `alert alert-${type} mb-4`;
        statusDiv.textContent = message;
        statusDiv.classList.remove('d-none');
    }

    function checkVerificationStatus(inquiryId) {
        showStatus('Checking verification status...', 'info');
        debugLog('Checking verification status for inquiry:', inquiryId);
        
        fetch('{{ route("account.kyc.check-status") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            debugLog('Status check response:', data);
            if (data.success) {
                if (data.status === 'completed') {
                    showStatus('Verification completed successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("account.kyc.index") }}';
                    }, 2000);
                } else if (data.status === 'failed') {
                    showStatus('Verification failed. Please try again.', 'danger');
                    setTimeout(() => {
                        window.location.href = '{{ route("account.kyc.create") }}';
                    }, 3000);
                } else {
                    showStatus('Verification in progress...', 'info');
                    setTimeout(() => checkVerificationStatus(inquiryId), data.polling_interval || 5000);
                }
            } else {
                showStatus('Error checking verification status: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            debugLog('Status check error:', error);
            console.error('Error:', error);
            showStatus('Error checking verification status. Please try again.', 'danger');
        });
    }

    let personaClient = null;
    let initializationAttempts = 0;
    const MAX_INITIALIZATION_ATTEMPTS = 3;

    function initializePersona() {
        debugLog('Attempting to initialize Persona...');
        
        if (initializationAttempts >= MAX_INITIALIZATION_ATTEMPTS) {
            debugLog('ERROR: Max initialization attempts reached');
            showStatus('Error: Failed to initialize verification system. Please refresh the page.', 'danger');
            return;
        }

        initializationAttempts++;

        if (!window.Persona) {
            debugLog(`Persona SDK not ready, attempt ${initializationAttempts}/${MAX_INITIALIZATION_ATTEMPTS}`);
            setTimeout(initializePersona, 1000);
            return;
        }

        try {
            debugLog('Creating Persona client...');
            personaClient = new window.Persona.Client({
                templateId: '{{ config("services.persona.template_id") }}',
                environment: '{{ config("app.env") === "production" ? "production" : "sandbox" }}',
                onLoad: (error) => {
                    if (error) {
                        debugLog('Persona load error:', error);
                        console.error('Error loading Persona:', error);
                        showStatus('Error loading verification system. Please try again.', 'danger');
                    } else {
                        debugLog('Persona loaded successfully');
                        startVerification();
                    }
                },
                onComplete: ({ inquiryId, status, fields }) => {
                    debugLog('Verification completed:', { inquiryId, status, fields });
                    showStatus('Verification submitted, checking status...', 'info');
                    checkVerificationStatus(inquiryId);
                },
                onCancel: () => {
                    debugLog('Verification cancelled by user');
                    showStatus('Verification cancelled. Redirecting...', 'warning');
                    setTimeout(() => {
                        window.location.href = '{{ route("account.kyc.index") }}';
                    }, 2000);
                },
                onError: (error) => {
                    debugLog('Verification error:', error);
                    console.error('Verification error:', error);
                    showStatus('An error occurred during verification: ' + error.message, 'danger');
                    setTimeout(() => {
                        window.location.href = '{{ route("account.kyc.index") }}';
                    }, 3000);
                }
            });
        } catch (error) {
            debugLog('Error creating Persona client:', error);
            setTimeout(initializePersona, 1000);
        }
    }

    function startVerification() {
        debugLog('Starting verification process...');
        fetch('{{ route("account.kyc.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            debugLog('Store response:', data);
            if (data.success && personaClient) {
                debugLog('Opening Persona iframe...');
                personaClient.open({
                    referenceId: '{{ auth()->id() }}',
                    fields: {
                        nameFirst: '{{ auth()->user()->name }}',
                        emailAddress: '{{ auth()->user()->email }}'
                    }
                });
            } else {
                showStatus('Error starting verification: ' + (data.message || 'Verification system not ready'), 'danger');
            }
        })
        .catch(error => {
            debugLog('Store error:', error);
            console.error('Error:', error);
            showStatus('Error starting verification. Please try again.', 'danger');
        });
    }

    // Initialize when Persona SDK loads
    function onPersonaLoad() {
        debugLog('Persona SDK loaded');
        debugLog('Environment:', '{{ config("app.env") }}');
        debugLog('Template ID:', '{{ config("services.persona.template_id") }}');
        initializePersona();
    }

    // Fallback initialization if onload doesn't trigger
    document.addEventListener('DOMContentLoaded', function() {
        debugLog('DOM Content Loaded');
        setTimeout(() => {
            if (!personaClient) {
                debugLog('Fallback initialization...');
                initializePersona();
            }
        }, 1000);
    });
</script>
@endpush

@push('styles')
<style>
    #persona-iframe {
        min-height: 600px;
    }
</style>
@endpush 