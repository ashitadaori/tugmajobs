<?php if(session('maintenance_warning')): ?>
<div class="alert alert-warning alert-dismissible fade show maintenance-banner" role="alert" style="margin: 0; border-radius: 0; border-left: 0; border-right: 0; border-top: 0;">
    <div class="container">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
            <div class="flex-grow-1">
                <strong>Maintenance Notice:</strong> <?php echo e(session('maintenance_warning')); ?>

            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
.maintenance-banner {
    position: sticky;
    top: 0;
    z-index: 1030;
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
<?php endif; ?>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/maintenance-banner.blade.php ENDPATH**/ ?>