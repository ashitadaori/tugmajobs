
<div id="toast-container" class="position-fixed" style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

<script>
// Simple Alert Bar Toast Notification System
(function() {
    'use strict';
    
    // Create toast function
    window.showToast = function(message, type = 'info', duration = 5000) {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;
        
        // Generate unique ID
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        
        // Color mapping - simple flat colors
        const config = {
            success: { 
                bgColor: '#d1f4e0',
                textColor: '#0f5132',
                borderColor: '#badbcc'
            },
            error: { 
                bgColor: '#f8d7da',
                textColor: '#842029',
                borderColor: '#f5c2c7'
            },
            warning: { 
                bgColor: '#fff3cd',
                textColor: '#664d03',
                borderColor: '#ffecb5'
            },
            info: { 
                bgColor: '#cfe2ff',
                textColor: '#084298',
                borderColor: '#b6d4fe'
            }
        };
        
        const settings = config[type] || config.info;
        
        // Create toast HTML - Compact top-right style
        const toastHTML = `
            <div id="${toastId}" class="compact-toast alert alert-dismissible fade show" role="alert" style="
                background-color: ${settings.bgColor};
                color: ${settings.textColor};
                border: 1px solid ${settings.borderColor};
                border-radius: 8px;
                margin-bottom: 10px;
                padding: 0.75rem 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                animation: slideInRight 0.3s ease-out;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                min-width: 300px;
                max-width: 400px;
            ">
                <div style="flex: 1; font-size: 0.875rem; line-height: 1.4;">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-sm" onclick="document.getElementById('${toastId}').remove()" aria-label="Close" style="
                    opacity: 0.5;
                    padding: 0.25rem;
                    margin-left: 0.75rem;
                    font-size: 0.75rem;
                "></button>
            </div>
        `;
        
        // Add to container
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        // Auto dismiss
        setTimeout(() => {
            const toastElement = document.getElementById(toastId);
            if (toastElement) {
                toastElement.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toastElement.remove(), 300);
            }
        }, duration);
    };
    
    // Check for session messages on page load
    document.addEventListener('DOMContentLoaded', function() {
        <?php if(session('success')): ?>
            showToast(<?php echo json_encode(session('success')); ?>, 'success');
        <?php endif; ?>
        
        <?php if(session('error')): ?>
            showToast(<?php echo json_encode(session('error')); ?>, 'error');
        <?php endif; ?>
        
        <?php if(session('warning')): ?>
            showToast(<?php echo json_encode(session('warning')); ?>, 'warning');
        <?php endif; ?>
        
        <?php if(session('info')): ?>
            showToast(<?php echo json_encode(session('info')); ?>, 'info');
        <?php endif; ?>
    });
})();
</script>

<style>
.compact-toast {
    width: auto;
}

/* Slide in from right */
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Slide out to right */
@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Mobile responsive */
@media (max-width: 576px) {
    #toast-container {
        top: 10px !important;
        right: 10px !important;
        left: 10px !important;
        max-width: none !important;
    }
    
    .compact-toast {
        min-width: auto !important;
        max-width: none !important;
    }
}
</style>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/components/toast-notifications.blade.php ENDPATH**/ ?>