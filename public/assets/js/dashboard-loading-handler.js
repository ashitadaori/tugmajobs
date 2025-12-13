/**
 * Dashboard Loading Handler
 * Manages loading states and smooth transitions for the jobseeker dashboard
 */

class DashboardLoadingHandler {
    constructor() {
        this.isLoading = true;
        this.loadingOverlay = null;
        this.contentContainer = null;
        this.minimumLoadingTime = 800; // Minimum loading time in milliseconds
        this.startTime = Date.now();
        
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupLoadingState());
        } else {
            this.setupLoadingState();
        }
    }

    setupLoadingState() {
        this.createLoadingOverlay();
        this.hideContent();
        this.showLoadingScreen();
        
        // Start loading dashboard content
        this.loadDashboardContent();
    }

    createLoadingOverlay() {
        // Remove any existing loading overlay
        const existingOverlay = document.getElementById('dashboard-loading-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }

        // Create new loading overlay
        this.loadingOverlay = document.createElement('div');
        this.loadingOverlay.id = 'dashboard-loading-overlay';
        this.loadingOverlay.className = 'dashboard-loading-overlay show';
        
        this.loadingOverlay.innerHTML = `
            <div class="loading-spinner">
                <div class="loading-dots">
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                </div>
            </div>
        `;
        
        document.body.appendChild(this.loadingOverlay);
    }

    hideContent() {
        // Don't hide content - just show loading indicator
        // Content will remain visible during loading
    }

    showLoadingScreen() {
        // Ensure loading overlay is visible
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.add('show');
        }
    }

    loadDashboardContent() {
        // Simulate content loading (replace with actual loading logic if needed)
        const loadingPromises = [
            this.loadUserProfile(),
            this.loadStats(),
            this.loadNotifications(),
            this.loadRecentActivity()
        ];

        Promise.all(loadingPromises)
            .then(() => this.handleLoadingComplete())
            .catch((error) => this.handleLoadingError(error));
    }

    async loadUserProfile() {
        // Simulate async loading
        return new Promise(resolve => {
            setTimeout(resolve, Math.random() * 300 + 100);
        });
    }

    async loadStats() {
        // Simulate async loading
        return new Promise(resolve => {
            setTimeout(resolve, Math.random() * 200 + 100);
        });
    }

    async loadNotifications() {
        // Simulate async loading
        return new Promise(resolve => {
            setTimeout(resolve, Math.random() * 150 + 50);
        });
    }

    async loadRecentActivity() {
        // Simulate async loading
        return new Promise(resolve => {
            setTimeout(resolve, Math.random() * 100 + 50);
        });
    }

    handleLoadingComplete() {
        const elapsedTime = Date.now() - this.startTime;
        const remainingTime = Math.max(0, this.minimumLoadingTime - elapsedTime);
        
        // Ensure minimum loading time for smooth UX
        setTimeout(() => {
            this.hideLoadingScreen();
            this.showContent();
        }, remainingTime);
    }

    handleLoadingError(error) {
        console.error('Dashboard loading error:', error);
        
        // Show error state
        if (this.loadingOverlay) {
            this.loadingOverlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="loading-text" style="color: #ef4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Loading Error
                    </div>
                    <div class="loading-subtitle">
                        Something went wrong. Retrying in a moment...
                    </div>
                    <button class="btn btn-primary mt-3" onclick="location.reload()">
                        Retry
                    </button>
                </div>
            `;
        }
        
        // Auto retry after 3 seconds
        setTimeout(() => {
            location.reload();
        }, 3000);
    }

    hideLoadingScreen() {
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.remove('show');
            
            // Remove overlay after transition
            setTimeout(() => {
                if (this.loadingOverlay && this.loadingOverlay.parentNode) {
                    this.loadingOverlay.parentNode.removeChild(this.loadingOverlay);
                }
            }, 300);
        }
    }

    showContent() {
        // Content is already visible, just apply smooth interactions
        this.initializeCardInteractions();

        // Mark loading as complete
        this.isLoading = false;

        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('dashboardLoaded'));
    }

    initializeCardInteractions() {
        // DISABLED: Card animations on page load to prevent disoriented UI
        // Cards will display immediately without fade-in effects

        // Add hover effects to action cards
        const actionCards = document.querySelectorAll('.modern-action-card');
        actionCards.forEach((card) => {
            // Add hover effects
            card.addEventListener('mouseenter', () => {
                if (!this.isLoading) {
                    card.style.transform = 'translateY(-8px) scale(1.02)';
                    card.style.boxShadow = '0 12px 28px rgba(0, 0, 0, 0.15)';
                }
            });

            card.addEventListener('mouseleave', () => {
                if (!this.isLoading) {
                    card.style.transform = 'translateY(0) scale(1)';
                    card.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
                }
            });
        });
    }

    // Public method to show loading for specific operations
    showOperationLoading() {
        if (this.loadingOverlay && this.loadingOverlay.parentNode) {
            return; // Already showing loading
        }

        this.createLoadingOverlay();
        this.showLoadingScreen();
    }

    // Public method to hide operation loading
    hideOperationLoading() {
        this.hideLoadingScreen();
    }
}

// DISABLED: Auto-initialize to prevent blink/flash animation on page reload
// This was causing disoriented UI when reloading the page
let dashboardLoader;

// document.addEventListener('DOMContentLoaded', () => {
//     // Only initialize on dashboard pages
//     if (document.querySelector('.modern-dashboard')) {
//         dashboardLoader = new DashboardLoadingHandler();
//     }
// });

// Expose for external use
window.DashboardLoader = DashboardLoadingHandler;

// DISABLED: Page reload and navigation animations to prevent UI disruption
// These cause the dashboard to appear disoriented during navigation
// Users will rely on browser's native loading indicators instead

// // Handle page reload animations
// window.addEventListener('beforeunload', () => {
//     if (dashboardLoader) {
//         dashboardLoader.showOperationLoading();
//     }
// });

// // Handle navigation loading
// document.addEventListener('click', (e) => {
//     const link = e.target.closest('a[href]');
//     if (link && link.getAttribute('href').startsWith('/') && !link.getAttribute('href').startsWith('//')) {
//         // Internal navigation
//         if (dashboardLoader && !dashboardLoader.isLoading) {
//             dashboardLoader.showOperationLoading();
//         }
//     }
// });

// // Progressive enhancement for forms
// document.addEventListener('submit', (e) => {
//     const form = e.target;
//     if (form && form.tagName === 'FORM') {
//         if (dashboardLoader && !dashboardLoader.isLoading) {
//             dashboardLoader.showOperationLoading();
//         }
//     }
// });
