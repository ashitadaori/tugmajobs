<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['user', 'size' => 'sm', 'showText' => false]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['user', 'size' => 'sm', 'showText' => false]); ?>
<?php foreach (array_filter((['user', 'size' => 'sm', 'showText' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $user = $user ?? Auth::user();
    $sizeClasses = [
        'xs' => 'fs-6',
        'sm' => 'fs-5', 
        'md' => 'fs-4',
        'lg' => 'fs-3'
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
?>

<?php if($user && $user->isKycVerified()): ?>
    <span class="verified-badge d-inline-flex align-items-center" 
          title="Verified Profile" 
          data-bs-toggle="tooltip">
        <i class="fas fa-check-circle text-success <?php echo e($sizeClass); ?> me-1"></i>
        <?php if($showText): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="fas fa-check-circle me-1"></i>Verified
            </span>
        <?php endif; ?>
    </span>
<?php endif; ?>

<style>
.verified-badge {
    animation: verifiedPulse 2s infinite;
}

@keyframes verifiedPulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.verified-badge:hover {
    animation: none;
    opacity: 1;
}
</style><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/verified-badge.blade.php ENDPATH**/ ?>