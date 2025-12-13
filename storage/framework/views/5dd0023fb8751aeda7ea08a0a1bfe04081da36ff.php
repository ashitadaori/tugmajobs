<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['job', 'size' => 'sm', 'showText' => false]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['job', 'size' => 'sm', 'showText' => false]); ?>
<?php foreach (array_filter((['job', 'size' => 'sm', 'showText' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $isSaved = auth()->check() && auth()->user()->role === 'jobseeker' ? $job->isSavedByUser(auth()->id()) : false;
    $buttonClass = $isSaved ? 'btn-success' : 'btn-outline-primary';
    $icon = $isSaved ? 'fas fa-bookmark' : 'far fa-bookmark';
    $text = $isSaved ? 'Saved ✓' : 'Save Job';
?>

<?php if(auth()->guard()->check()): ?>
    <?php if(auth()->user()->role === 'jobseeker'): ?>
        <button type="button"
                class="btn <?php echo e($buttonClass); ?> btn-<?php echo e($size); ?> save-job-btn <?php echo e($attributes->get('class')); ?>"
                data-job-id="<?php echo e($job->id); ?>"
                data-saved="<?php echo e($isSaved ? 'true' : 'false'); ?>"
                aria-label="<?php echo e($text); ?>">
            <i class="<?php echo e($icon); ?><?php echo e($showText ? ' me-1' : ''); ?>"></i>
            <?php if($showText): ?>
                <span class="save-text"><?php echo e($text); ?></span>
            <?php endif; ?>
        </button>
    <?php endif; ?>
<?php endif; ?>

<?php if(auth()->guard()->guest()): ?>
    <button type="button"
            class="btn btn-outline-secondary btn-<?php echo e($size); ?> <?php echo e($attributes->get('class')); ?>"
            onclick="showLoginPrompt()"
            aria-label="Login to save jobs">
        <i class="far fa-bookmark<?php echo e($showText ? ' me-1' : ''); ?>"></i>
        <?php if($showText): ?>
            <span>Save Job</span>
        <?php endif; ?>
    </button>
<?php endif; ?>

<?php $__env->startPush('styles'); ?>
<style>
.save-job-toast {
    transition: all 0.3s ease-in-out !important;
    transform: translateX(0) !important;
}

.save-job-toast.removing {
    opacity: 0 !important;
    transform: translateX(100%) !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Save job functionality with debounce
    let isProcessing = false;
    
    document.querySelectorAll('.save-job-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Prevent multiple rapid clicks
            if (isProcessing || this.disabled) {
                console.log('Click ignored - already processing');
                return;
            }
            
            const jobId = this.getAttribute('data-job-id');
            const isSaved = this.getAttribute('data-saved') === 'true';
            
            // If job is already saved, ask for confirmation to remove
            if (isSaved) {
                if (!confirm('Remove this job from your saved list?')) {
                    return; // User cancelled
                }
            }
            
            // Set processing flag
            isProcessing = true;
            
            // Show loading state
            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + (<?php echo e($showText ? 'true' : 'false'); ?> ? '<span>Processing...</span>' : '');
            this.disabled = true;
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found');
                showSaveMessage('Security token not found. Please refresh the page.', 'error');
                this.innerHTML = originalContent;
                this.disabled = false;
                return;
            }
            
            // Make AJAX request
            fetch('<?php echo e(route("account.saved-jobs.toggle")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    job_id: jobId
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    // Update button state
                    const newIsSaved = data.is_saved;
                    const newButtonClass = newIsSaved ? 'btn-success' : 'btn-outline-primary';
                    const newIcon = newIsSaved ? 'fas fa-bookmark' : 'far fa-bookmark';
                    const newText = newIsSaved ? 'Saved ✓' : 'Save Job';
                    
                    // Update classes - remove old classes first
                    this.classList.remove('btn-success', 'btn-outline-primary', 'btn-danger', 'btn-outline-danger');
                    if (newIsSaved) {
                        this.classList.add('btn-success');
                    } else {
                        this.classList.add('btn-outline-primary');
                    }
                    
                    // Update content
                    this.innerHTML = `<i class="${newIcon} me-1"></i>` + (<?php echo e($showText ? 'true' : 'false'); ?> ? `<span class="save-text">${newText}</span>` : '');
                    
                    // Update data attribute
                    this.setAttribute('data-saved', newIsSaved ? 'true' : 'false');
                    this.setAttribute('aria-label', newText);
                    
                    // Show success message with action
                    const actionMessage = data.action === 'saved' ? 
                        '✅ Job saved successfully!' : 
                        '🗑️ Job removed from your list!';
                    showSaveMessage(actionMessage, 'success');
                    
                    // Update saved count if element exists
                    const savedCountElement = document.querySelector('.saved-jobs-count');
                    if (savedCountElement) {
                        savedCountElement.textContent = data.saved_count;
                    }
                    
                    console.log(`Job ${data.action}. New state: ${newIsSaved ? 'SAVED' : 'NOT SAVED'}`);
                } else {
                    showSaveMessage(data.message, 'error');
                    this.innerHTML = originalContent;
                }
                
                // Reset processing state with a small delay to prevent rapid clicking
                setTimeout(() => {
                    this.disabled = false;
                    isProcessing = false;
                }, 500); // 500ms delay
            })
            .catch(error => {
                console.error('Detailed error:', error);
                console.error('Error message:', error.message);
                showSaveMessage(`Error: ${error.message}`, 'error');
                this.innerHTML = originalContent;
                this.disabled = false;
                isProcessing = false; // Reset processing state
            });
        });
    });
});

function showLoginPrompt() {
    if (confirm('You need to login as a jobseeker to save jobs. Would you like to login now?')) {
        window.location.href = '<?php echo e(route("login")); ?>';
    }
}

function showSaveMessage(message, type) {
    // Remove any existing save messages first
    const existingToasts = document.querySelectorAll('.save-job-toast');
    existingToasts.forEach(toast => {
        toast.remove();
    });
    
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed save-job-toast`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-dismiss after 2 seconds (shorter time)
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 2000);
}
</script>
<?php $__env->stopPush(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/save-job-button.blade.php ENDPATH**/ ?>