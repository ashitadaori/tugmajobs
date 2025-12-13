/**
 * Responsive Sidebar Fix JavaScript
 * Comprehensive solution for sidebar responsiveness and interactions
 */

class ResponsiveSidebarFix {
    constructor() {
        this.sidebar = document.querySelector('.employer-sidebar');
        this.mainContent = document.querySelector('.employer-main-content');
        this.mobileToggle = document.querySelector('.mobile-sidebar-toggle');
        this.sidebarOverlay = document.querySelector('.sidebar-overlay');
        this.collapseBtn = document.querySelector('.sidebar-collapse-btn');
        this.navbar = document.querySelector('.employer-dashboard-navbar');
        
        this.isCollapsed = false;
        this.isMobileOpen = false;
        this.currentBreakpoint = this.getCurrentBreakpoint();
        
        this.breakpoints = {
            mobile: 768,
            tablet: 1024,
            desktop: 1025
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.handleResize();
        this.restoreState();
        this.setupKeyboardNavigation();
        this.setupTouchGestures();
        this.setupAccessibility();
    }
    
    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        // Mobile toggle button
        if (this.mobileToggle) {
            this.mobileToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMobileSidebar();
            });
        }
        
        // Sidebar overlay
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => {
                this.closeMobileSidebar();
            });
        }
        
        // Collapse button
        if (this.collapseBtn) {
            this.collapseBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleCollapse();
            });
        }
        
        // Window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.handleResize();
            }, 150);
        });
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobileOpen) {
                this.closeMobileSidebar();
            }
        });
        
        // Click outside sidebar on mobile
        document.addEventListener('click', (e) => {
            if (this.isMobile() && this.isMobileOpen) {
                if (!this.sidebar?.contains(e.target) && 
                    !this.mobileToggle?.contains(e.target)) {
                    this.closeMobileSidebar();
                }
            }
        });
        
        // Navigation links
        this.setupNavigationLinks();
    }
    
    /**
     * Setup navigation link interactions
     */
    setupNavigationLinks() {
        const navLinks = document.querySelectorAll('.employer-sidebar .nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Close mobile sidebar when navigating
                if (this.isMobile() && this.isMobileOpen) {
                    setTimeout(() => {
                        this.closeMobileSidebar();
                    }, 150);
                }
            });
        });
    }
    
    /**
     * Handle window resize
     */
    handleResize() {
        const newBreakpoint = this.getCurrentBreakpoint();
        const breakpointChanged = newBreakpoint !== this.currentBreakpoint;
        
        if (breakpointChanged) {
            this.handleBreakpointChange(this.currentBreakpoint, newBreakpoint);
            this.currentBreakpoint = newBreakpoint;
        }
        
        this.updateLayout();
        this.updateButtonVisibility();
    }
    
    /**
     * Handle breakpoint changes
     */
    handleBreakpointChange(oldBreakpoint, newBreakpoint) {
        // Switching to mobile
        if (newBreakpoint === 'mobile' && oldBreakpoint !== 'mobile') {
            this.closeMobileSidebar();
            this.expandSidebar(); // Force expand on mobile
            document.body.classList.add('mobile-layout');
            document.body.classList.remove('tablet-layout', 'desktop-layout');
        }
        
        // Switching to tablet
        else if (newBreakpoint === 'tablet') {
            this.closeMobileSidebar();
            document.body.classList.add('tablet-layout');
            document.body.classList.remove('mobile-layout', 'desktop-layout');
        }
        
        // Switching to desktop
        else if (newBreakpoint === 'desktop') {
            this.closeMobileSidebar();
            document.body.classList.add('desktop-layout');
            document.body.classList.remove('mobile-layout', 'tablet-layout');
        }
    }
    
    /**
     * Update layout based on current state
     */
    updateLayout() {
        if (!this.sidebar || !this.mainContent) return;
        
        const breakpoint = this.getCurrentBreakpoint();
        
        if (breakpoint === 'mobile') {
            // Mobile layout
            this.mainContent.style.marginLeft = '0';
            this.mainContent.style.width = '100%';
            
            if (this.navbar) {
                this.navbar.style.paddingLeft = '4rem';
            }
        } else if (breakpoint === 'tablet') {
            // Tablet layout
            const sidebarWidth = this.isCollapsed ? '70px' : '240px';
            this.mainContent.style.marginLeft = sidebarWidth;
            this.mainContent.style.width = `calc(100% - ${sidebarWidth})`;
            
            if (this.navbar) {
                this.navbar.style.paddingLeft = '2rem';
            }
        } else {
            // Desktop layout
            const sidebarWidth = this.isCollapsed ? '80px' : '280px';
            this.mainContent.style.marginLeft = sidebarWidth;
            this.mainContent.style.width = `calc(100% - ${sidebarWidth})`;
            
            if (this.navbar) {
                this.navbar.style.paddingLeft = '2rem';
            }
        }
    }
    
    /**
     * Update button visibility
     */
    updateButtonVisibility() {
        const breakpoint = this.getCurrentBreakpoint();
        
        // Mobile toggle button
        if (this.mobileToggle) {
            this.mobileToggle.style.display = breakpoint === 'mobile' ? 'flex' : 'none';
        }
        
        // Collapse button
        if (this.collapseBtn) {
            this.collapseBtn.style.display = breakpoint === 'mobile' ? 'none' : 'flex';
            
            if (breakpoint !== 'mobile') {
                const leftPosition = this.getCollapseButtonPosition();
                this.collapseBtn.style.left = leftPosition;
            }
        }
    }
    
    /**
     * Get collapse button position based on breakpoint and state
     */
    getCollapseButtonPosition() {
        const breakpoint = this.getCurrentBreakpoint();
        
        if (breakpoint === 'tablet') {
            return this.isCollapsed ? '50px' : '220px';
        } else {
            return this.isCollapsed ? '60px' : '260px';
        }
    }
    
    /**
     * Toggle mobile sidebar
     */
    toggleMobileSidebar() {
        if (!this.isMobile()) return;
        
        if (this.isMobileOpen) {
            this.closeMobileSidebar();
        } else {
            this.openMobileSidebar();
        }
    }
    
    /**
     * Open mobile sidebar
     */
    openMobileSidebar() {
        if (!this.isMobile() || !this.sidebar) return;
        
        this.isMobileOpen = true;
        
        // Add classes
        this.sidebar.classList.add('show', 'mobile-open', 'active');
        if (this.sidebarOverlay) {
            this.sidebarOverlay.classList.add('show', 'active');
        }
        
        // Prevent body scroll
        document.body.classList.add('sidebar-open');
        document.body.style.overflow = 'hidden';
        
        // Update mobile toggle icon
        this.updateMobileToggleIcon(true);
        
        // Focus management
        setTimeout(() => {
            const firstNavLink = this.sidebar.querySelector('.nav-link');
            if (firstNavLink) {
                firstNavLink.focus();
            }
        }, 300);
        
        // Update ARIA attributes
        this.sidebar.setAttribute('aria-hidden', 'false');
        if (this.mobileToggle) {
            this.mobileToggle.setAttribute('aria-expanded', 'true');
        }
    }
    
    /**
     * Close mobile sidebar
     */
    closeMobileSidebar() {
        if (!this.sidebar) return;
        
        this.isMobileOpen = false;
        
        // Remove classes
        this.sidebar.classList.remove('show', 'mobile-open', 'active');
        if (this.sidebarOverlay) {
            this.sidebarOverlay.classList.remove('show', 'active');
        }
        
        // Restore body scroll
        document.body.classList.remove('sidebar-open');
        document.body.style.overflow = '';
        
        // Update mobile toggle icon
        this.updateMobileToggleIcon(false);
        
        // Return focus to toggle button
        if (this.mobileToggle && this.isMobile()) {
            this.mobileToggle.focus();
        }
        
        // Update ARIA attributes
        this.sidebar.setAttribute('aria-hidden', 'true');
        if (this.mobileToggle) {
            this.mobileToggle.setAttribute('aria-expanded', 'false');
        }
    }
    
    /**
     * Update mobile toggle icon
     */
    updateMobileToggleIcon(isOpen) {
        if (!this.mobileToggle) return;
        
        const icon = this.mobileToggle.querySelector('i');
        if (icon) {
            icon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
        }
    }
    
    /**
     * Toggle sidebar collapse (desktop/tablet only)
     */
    toggleCollapse() {
        if (this.isMobile()) return;
        
        this.isCollapsed = !this.isCollapsed;
        this.updateCollapseState();
        this.saveState();
    }
    
    /**
     * Update collapse state
     */
    updateCollapseState() {
        if (!this.sidebar || this.isMobile()) return;
        
        // Update body class
        document.body.classList.toggle('sidebar-collapsed', this.isCollapsed);
        
        // Update layout
        this.updateLayout();
        
        // Update collapse button icon
        if (this.collapseBtn) {
            const icon = this.collapseBtn.querySelector('i');
            if (icon) {
                icon.style.transform = this.isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }
        
        // Trigger resize event for other components
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 300);
    }
    
    /**
     * Expand sidebar
     */
    expandSidebar() {
        if (this.isCollapsed) {
            this.isCollapsed = false;
            this.updateCollapseState();
            this.saveState();
        }
    }
    
    /**
     * Collapse sidebar
     */
    collapseSidebar() {
        if (!this.isCollapsed && !this.isMobile()) {
            this.isCollapsed = true;
            this.updateCollapseState();
            this.saveState();
        }
    }
    
    /**
     * Setup keyboard navigation
     */
    setupKeyboardNavigation() {
        // Tab navigation within sidebar
        if (this.sidebar) {
            this.sidebar.addEventListener('keydown', (e) => {
                if (e.key === 'Tab' && this.isMobileOpen) {
                    this.trapFocus(e);
                }
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + B to toggle sidebar
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                if (this.isMobile()) {
                    this.toggleMobileSidebar();
                } else {
                    this.toggleCollapse();
                }
            }
        });
    }
    
    /**
     * Trap focus within sidebar when open on mobile
     */
    trapFocus(e) {
        const focusableElements = this.sidebar.querySelectorAll(
            'a, button, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        if (e.shiftKey && document.activeElement === firstElement) {
            e.preventDefault();
            lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
            e.preventDefault();
            firstElement.focus();
        }
    }
    
    /**
     * Setup touch gestures for mobile
     */
    setupTouchGestures() {
        if (!this.isMobile()) return;
        
        let startX = 0;
        let startY = 0;
        let isSwipeGesture = false;
        
        // Touch start
        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isSwipeGesture = false;
        }, { passive: true });
        
        // Touch move
        document.addEventListener('touchmove', (e) => {
            if (!startX || !startY) return;
            
            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            const diffX = startX - currentX;
            const diffY = startY - currentY;
            
            // Check if it's a horizontal swipe
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                isSwipeGesture = true;
                
                // Swipe right to open sidebar (from left edge)
                if (diffX < 0 && startX < 50 && !this.isMobileOpen) {
                    this.openMobileSidebar();
                }
                
                // Swipe left to close sidebar
                if (diffX > 0 && this.isMobileOpen) {
                    this.closeMobileSidebar();
                }
            }
        }, { passive: true });
        
        // Touch end
        document.addEventListener('touchend', () => {
            startX = 0;
            startY = 0;
            isSwipeGesture = false;
        }, { passive: true });
    }
    
    /**
     * Setup accessibility features
     */
    setupAccessibility() {
        // Add ARIA attributes
        if (this.sidebar) {
            this.sidebar.setAttribute('role', 'navigation');
            this.sidebar.setAttribute('aria-label', 'Main navigation');
            this.sidebar.setAttribute('aria-hidden', this.isMobile() ? 'true' : 'false');
        }
        
        if (this.mobileToggle) {
            this.mobileToggle.setAttribute('aria-controls', 'employer-sidebar');
            this.mobileToggle.setAttribute('aria-expanded', 'false');
        }
        
        if (this.collapseBtn) {
            this.collapseBtn.setAttribute('aria-controls', 'employer-sidebar');
            this.collapseBtn.setAttribute('aria-expanded', this.isCollapsed ? 'false' : 'true');
        }
    }
    
    /**
     * Get current breakpoint
     */
    getCurrentBreakpoint() {
        const width = window.innerWidth;
        
        if (width <= this.breakpoints.mobile) {
            return 'mobile';
        } else if (width <= this.breakpoints.tablet) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
    
    /**
     * Check if current breakpoint is mobile
     */
    isMobile() {
        return this.getCurrentBreakpoint() === 'mobile';
    }
    
    /**
     * Check if current breakpoint is tablet
     */
    isTablet() {
        return this.getCurrentBreakpoint() === 'tablet';
    }
    
    /**
     * Check if current breakpoint is desktop
     */
    isDesktop() {
        return this.getCurrentBreakpoint() === 'desktop';
    }
    
    /**
     * Save state to localStorage
     */
    saveState() {
        localStorage.setItem('employer-sidebar-collapsed', this.isCollapsed.toString());
    }
    
    /**
     * Restore state from localStorage
     */
    restoreState() {
        const savedState = localStorage.getItem('employer-sidebar-collapsed');
        
        if (savedState === 'true' && !this.isMobile()) {
            this.isCollapsed = true;
            this.updateCollapseState();
        }
    }
    
    /**
     * Public API methods
     */
    
    // Open sidebar
    open() {
        if (this.isMobile()) {
            this.openMobileSidebar();
        } else {
            this.expandSidebar();
        }
    }
    
    // Close sidebar
    close() {
        if (this.isMobile()) {
            this.closeMobileSidebar();
        } else {
            this.collapseSidebar();
        }
    }
    
    // Toggle sidebar
    toggle() {
        if (this.isMobile()) {
            this.toggleMobileSidebar();
        } else {
            this.toggleCollapse();
        }
    }
    
    // Check if sidebar is open
    isOpen() {
        if (this.isMobile()) {
            return this.isMobileOpen;
        } else {
            return !this.isCollapsed;
        }
    }
    
    // Get current state
    getState() {
        return {
            isCollapsed: this.isCollapsed,
            isMobileOpen: this.isMobileOpen,
            breakpoint: this.currentBreakpoint,
            isMobile: this.isMobile(),
            isTablet: this.isTablet(),
            isDesktop: this.isDesktop()
        };
    }
    
    // Refresh layout
    refresh() {
        this.handleResize();
    }
    
    // Destroy instance
    destroy() {
        // Remove event listeners and clean up
        document.body.classList.remove('sidebar-open', 'sidebar-collapsed', 'mobile-layout', 'tablet-layout', 'desktop-layout');
        document.body.style.overflow = '';
        
        if (this.sidebar) {
            this.sidebar.classList.remove('show', 'mobile-open', 'active');
        }
        
        if (this.sidebarOverlay) {
            this.sidebarOverlay.classList.remove('show', 'active');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.responsiveSidebarFix = new ResponsiveSidebarFix();
});

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && window.responsiveSidebarFix) {
        window.responsiveSidebarFix.refresh();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ResponsiveSidebarFix;
}