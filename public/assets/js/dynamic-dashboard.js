/**
 * Dynamic Dashboard JavaScript
 * Handles interactive features for the employer dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard features
    initStatsAnimation();
    initNotificationPolling();
    initQuickActions();
    initTooltips();
    initProgressBars();
    
    // Auto-refresh data every 5 minutes
    setInterval(refreshDashboardData, 300000);
});

/**
 * Animate stats cards on page load
 */
function initStatsAnimation() {
    const statsValues = document.querySelectorAll('.stats-value');
    
    statsValues.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        if (isNaN(finalValue)) return;
        
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 50);
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            stat.textContent = currentValue;
        }, 30);
    });
}

/**
 * Initialize notification polling
 */
function initNotificationPolling() {
    // Check for new notifications every 30 seconds
    setInterval(checkForNewNotifications, 30000);
}

/**
 * Check for new notifications
 */
function checkForNewNotifications() {
    fetch('/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.count);
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badges = document.querySelectorAll('.notification-badge');
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    });
}

/**
 * Initialize quick actions
 */
function initQuickActions() {
    const quickActionCards = document.querySelectorAll('.quick-action-card');
    
    quickActionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Initialize progress bars with animation
 */
function initProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    // Animate progress bars on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target;
                const width = progressBar.style.width;
                progressBar.style.width = '0%';
                
                setTimeout(() => {
                    progressBar.style.width = width;
                }, 100);
                
                observer.unobserve(progressBar);
            }
        });
    });
    
    progressBars.forEach(bar => {
        observer.observe(bar);
    });
}

/**
 * Refresh dashboard data
 */
function refreshDashboardData() {
    // Show loading indicator
    showLoadingIndicator();
    
    fetch('/employer/dashboard/data', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        updateDashboardStats(data);
        hideLoadingIndicator();
    })
    .catch(error => {
        console.error('Error refreshing dashboard data:', error);
        hideLoadingIndicator();
    });
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats(data) {
    // Update stats values
    const statsMapping = {
        'posted-jobs': data.postedJobs,
        'active-jobs': data.activeJobs,
        'total-applications': data.totalApplications,
        'pending-applications': data.pendingApplications
    };
    
    Object.keys(statsMapping).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"] .stats-value`);
        if (element && statsMapping[key] !== undefined) {
            animateValueChange(element, parseInt(element.textContent), statsMapping[key]);
        }
    });
    
    // Update progress bars
    updateProgressBars(data);
    
    // Update trend indicators
    updateTrendIndicators(data);
}

/**
 * Animate value changes
 */
function animateValueChange(element, fromValue, toValue) {
    if (fromValue === toValue) return;
    
    const difference = toValue - fromValue;
    const steps = 20;
    const stepValue = difference / steps;
    let currentStep = 0;
    
    const timer = setInterval(() => {
        currentStep++;
        const currentValue = Math.round(fromValue + (stepValue * currentStep));
        element.textContent = currentValue;
        
        if (currentStep >= steps) {
            element.textContent = toValue;
            clearInterval(timer);
        }
    }, 50);
}

/**
 * Update progress bars
 */
function updateProgressBars(data) {
    // Update job posting progress
    const jobProgress = document.querySelector('[data-progress="jobs"]');
    if (jobProgress && data.postedJobs !== undefined) {
        const percentage = Math.min(data.postedJobs * 10, 100);
        jobProgress.style.width = `${percentage}%`;
    }
    
    // Update active jobs ratio
    const activeProgress = document.querySelector('[data-progress="active"]');
    if (activeProgress && data.postedJobs > 0) {
        const percentage = (data.activeJobs / data.postedJobs) * 100;
        activeProgress.style.width = `${percentage}%`;
    }
    
    // Update applications progress
    const applicationsProgress = document.querySelector('[data-progress="applications"]');
    if (applicationsProgress && data.totalApplications !== undefined) {
        const percentage = Math.min(data.totalApplications * 2, 100);
        applicationsProgress.style.width = `${percentage}%`;
    }
    
    // Update pending ratio
    const pendingProgress = document.querySelector('[data-progress="pending"]');
    if (pendingProgress && data.totalApplications > 0) {
        const percentage = (data.pendingApplications / data.totalApplications) * 100;
        pendingProgress.style.width = `${percentage}%`;
    }
}

/**
 * Update trend indicators
 */
function updateTrendIndicators(data) {
    // This would typically compare with previous period data
    // For now, we'll just ensure the trends are visible
    const trendElements = document.querySelectorAll('.stats-trend');
    trendElements.forEach(trend => {
        trend.style.opacity = '1';
    });
}

/**
 * Show loading indicator
 */
function showLoadingIndicator() {
    let indicator = document.getElementById('dashboard-loading');
    
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'dashboard-loading';
        indicator.className = 'dashboard-loading-indicator';
        indicator.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-sync-alt fa-spin"></i>
                <span>Updating dashboard...</span>
            </div>
        `;
        document.body.appendChild(indicator);
    }
    
    indicator.classList.add('show');
}

/**
 * Hide loading indicator
 */
function hideLoadingIndicator() {
    const indicator = document.getElementById('dashboard-loading');
    if (indicator) {
        indicator.classList.remove('show');
        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.parentNode.removeChild(indicator);
            }
        }, 300);
    }
}

/**
 * Handle quick action clicks
 */
function handleQuickAction(action, element) {
    // Add click animation
    element.style.transform = 'scale(0.95)';
    setTimeout(() => {
        element.style.transform = 'scale(1)';
    }, 150);
    
    // Track action for analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'quick_action_click', {
            'action_type': action,
            'page_location': window.location.href
        });
    }
}

/**
 * Handle notification clicks
 */
function handleNotificationClick(notificationId, actionUrl) {
    // Mark notification as read
    fetch(`/notifications/mark-as-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && actionUrl) {
            window.location.href = actionUrl;
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        if (actionUrl) {
            window.location.href = actionUrl;
        }
    });
}

/**
 * Initialize keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N: New job
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.location.href = '/employer/jobs/create';
        }
        
        // Ctrl/Cmd + A: Applications
        if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
            e.preventDefault();
            window.location.href = '/employer/applications';
        }
        
        // Ctrl/Cmd + D: Dashboard
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            window.location.href = '/employer/dashboard';
        }
    });
}

/**
 * Handle responsive behavior
 */
function handleResponsive() {
    const handleResize = () => {
        const isMobile = window.innerWidth < 768;
        const statsCards = document.querySelectorAll('.stats-card');
        
        statsCards.forEach(card => {
            if (isMobile) {
                card.classList.add('mobile-optimized');
            } else {
                card.classList.remove('mobile-optimized');
            }
        });
    };
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Initial call
}

/**
 * Initialize error handling
 */
function initErrorHandling() {
    window.addEventListener('error', function(e) {
        console.error('Dashboard error:', e.error);
        
        // Show user-friendly error message
        showErrorMessage('Something went wrong. Please refresh the page.');
    });
    
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Dashboard promise rejection:', e.reason);
        
        // Show user-friendly error message
        showErrorMessage('Network error. Please check your connection.');
    });
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    errorDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    errorDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(errorDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}

// Initialize additional features
document.addEventListener('DOMContentLoaded', function() {
    initKeyboardShortcuts();
    handleResponsive();
    initErrorHandling();
});

// Export functions for global use
window.DashboardUtils = {
    refreshData: refreshDashboardData,
    handleQuickAction: handleQuickAction,
    handleNotificationClick: handleNotificationClick,
    showErrorMessage: showErrorMessage
};