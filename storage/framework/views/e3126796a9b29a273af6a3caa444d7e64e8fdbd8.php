<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['user', 'showActions' => true, 'compact' => false]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['user', 'showActions' => true, 'compact' => false]); ?>
<?php foreach (array_filter((['user', 'showActions' => true, 'compact' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $user = $user ?? Auth::user();
    $statusConfig = [
        'pending' => [
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'warning',
            'bg' => 'warning-subtle',
            'title' => 'Identity Verification Required',
            'message' => '⚠️ You must complete KYC verification before you can apply for jobs. Verify your identity now to start applying.',
            'action' => 'Start Verification'
        ],
        'in_progress' => [
            'icon' => 'fas fa-hourglass-half',
            'color' => 'warning',
            'bg' => 'warning-subtle',
            'title' => 'Verification in Progress',
            'message' => 'Your identity verification is being processed. This usually takes 2-5 minutes.',
            'action' => 'Check Status'
        ],
        'verified' => [
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'bg' => 'success-subtle',
            'title' => 'Identity Verified',
            'message' => 'Your identity has been successfully verified. You have access to all features.',
            'action' => null
        ],
        'failed' => [
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'bg' => 'danger-subtle',
            'title' => 'Verification Failed',
            'message' => '❌ We were unable to verify your identity. You cannot apply for jobs until verification is complete. Please try again with clear documents.',
            'action' => 'Try Again'
        ],
        'expired' => [
            'icon' => 'fas fa-clock',
            'color' => 'dark',
            'bg' => 'dark-subtle',
            'title' => 'Verification Expired',
            'message' => 'Your verification session has expired. Please start a new verification.',
            'action' => 'Start New Verification'
        ]
    ];
    
    $config = $statusConfig[$user->kyc_status] ?? $statusConfig['pending'];
?>

<div class="card <?php echo e($compact ? 'border-0 shadow-sm' : 'mb-4'); ?>">
    <?php if(!$compact): ?>
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center">
            <i class="<?php echo e($config['icon']); ?> text-<?php echo e($config['color']); ?> me-2"></i>
            <h5 class="mb-0">Identity Verification</h5>
            <?php if($user->isKycVerified()): ?>
                <span class="badge bg-success ms-auto">
                    <i class="fas fa-check-circle me-1"></i>Verified
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card-body <?php echo e($compact ? 'p-3' : ''); ?>">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0 me-3">
                <div class="verification-icon bg-<?php echo e($config['bg']); ?> text-<?php echo e($config['color']); ?> rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: <?php echo e($compact ? '40px' : '48px'); ?>; height: <?php echo e($compact ? '40px' : '48px'); ?>;">
                    <i class="<?php echo e($config['icon']); ?>" style="font-size: <?php echo e($compact ? '1.2rem' : '1.5rem'); ?>;"></i>
                </div>
            </div>
            
            <div class="flex-grow-1">
                <h6 class="mb-1 <?php echo e($compact ? 'fs-6' : ''); ?>"><?php echo e($config['title']); ?></h6>
                <p class="text-muted mb-0 <?php echo e($compact ? 'small' : ''); ?>"><?php echo e($config['message']); ?></p>
                
                <?php if($user->kyc_verified_at && $user->isKycVerified()): ?>
                    <small class="text-success">
                        <i class="fas fa-calendar-check me-1"></i>
                        Verified on <?php echo e($user->kyc_verified_at->format('M d, Y')); ?>

                    </small>
                <?php endif; ?>
                
                <?php if($showActions && $config['action']): ?>
                    <div class="mt-3">
                        <?php if($user->kyc_status === 'in_progress'): ?>
                            <button class="btn btn-<?php echo e($config['color']); ?> btn-sm me-2" onclick="checkKycStatus('<?php echo e($user->kyc_session_id); ?>')">
                                <i class="fas fa-sync me-1"></i><?php echo e($config['action']); ?>

                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="window.resetKycVerification ? window.resetKycVerification() : alert('Reset function not available')">
                                <i class="fas fa-redo me-1"></i>Start Over
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-<?php echo e($config['color']); ?> btn-sm" onclick="startInlineVerification()">
                                <i class="<?php echo e($config['icon']); ?> me-1"></i><?php echo e($config['action']); ?>

                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if($showActions): ?>
<!-- Include KYC inline verification script -->
<script src="<?php echo e(asset('assets/js/kyc-inline-verification.js')); ?>"></script>

<script>
// Set current user ID for verification polling
window.currentUserId = <?php echo e(Auth::id()); ?>;

function checkKycStatus(sessionId) {
    if (!sessionId) {
        alert('No session ID available');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking...';
    btn.disabled = true;
    
    fetch('<?php echo e(route("kyc.check-status")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({
            session_id: sessionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'completed') {
            window.location.reload();
        } else if (data.status === 'failed') {
            alert('Verification failed. Please try again.');
            window.location.reload();
        } else {
            alert('Still processing. Please wait a moment and try again.');
        }
    })
    .catch(error => {
        alert('Error checking status. Please try again.');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Enhanced check status function that uses the global function if available
function enhancedCheckStatus() {
    if (typeof window.checkVerificationComplete === 'function') {
        window.checkVerificationComplete();
    } else if (typeof window.checkVerificationStatus === 'function') {
        window.checkVerificationStatus();
    } else {
        // Fallback to the original function
        const sessionId = '<?php echo e($user->kyc_session_id); ?>';
        if (sessionId) {
            checkKycStatus(sessionId);
        } else {
            alert('No session available for status check');
        }
    }
}

// Debug function availability on load
document.addEventListener('DOMContentLoaded', function() {
    console.log('KYC Status Card loaded');
    
    // Check if KYC functions are available
    const functions = ['startInlineVerification', 'checkVerificationComplete', 'resetKycVerification'];
    functions.forEach(funcName => {
        if (typeof window[funcName] === 'function') {
            console.log(`✓ ${funcName} is available in status card`);
        } else {
            console.log(`✗ ${funcName} is NOT available in status card`);
        }
    });
});
</script>
<?php endif; ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/kyc-status-card.blade.php ENDPATH**/ ?>