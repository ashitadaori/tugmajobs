/**
 * Responsive Sidebar Collapse JavaScript
 * Enhanced collapse button functionality with responsive behavior
 */

class ResponsiveSidebarCollapse {
    constructor() {
        this.sidebar = document.querySelector('.employer-sidebar');
        this.mainContent = document.querySelector('.employer-main-content');
        this.collapseBtn = document.querySelector('.sidebar-collapse-btn');
        this.mobileToggle = document.querySelector('.mobile-sidebar-toggle');
        this.sidebarOverlay = document.querySelector('.sidebar-overlay');
        
        this.isCollapsed = false;
        this.isMobile = false;
        this.isTablet = false;
        this.isDesktop = false;
        
        this.breakpoints = {
            mobile: 768,
            tablet: 1024,
            desktop: 1025
        };
        
        this.init();
    }
    
    init() {
        this.updateScreenSize();
        this.restoreState();
        this.setupEventListeners();
        this.setupKeyboardNavigation();
        this.setupTooltips();
        this.setupNavigation();
        this.addPulseForNewUsers();
    }
    
    setupNavigation() {
        // Handle navigation link clicks
        const navLinks = document.querySelectorAll('.employer-sidebar .nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Close mobile sidebar on navigation
                if (this.isMobile) {
                    this.closeMobileSidebar();
                }
                
                // Update active states
                this.updateActiveNavigation(link);
            });
        });
        
        // Sync active states on load
        this.syncActiveStates();
    }
    
    updateActiveNavigation(clickedLink) {
        const navLinks = document.querySelectorAll('.employer-sidebar .nav-link');
        
        // Remove active state from all links
        navLinks.forEach(link => {
            link.classList.remove('active');
            link.setAttribute('aria-current', 'false');
        });
        
        // Add active state to clicked link
        clickedLink.classList.add('active');
        clickedLink.setAttribute('aria-current', 'page');
    }
    
    syncActiveStates() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.employer-sidebar .nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            const isActive = this.isLinkActive(href, currentPath);
            
            if (isActive) {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            } else {
                link.classList.remove('active');
                link.setAttribute('aria-current', 'false');
            }
        });
    }
    
    isLinkActive(href, currentPath) {
        if (!href) return false;
        
        // Exact match
        if (href === currentPath) return true;
        
        // Pattern matching for different sections
        if (href.includes('/employer/dashboard') && currentPath.includes('/employer/dashboard')) return true;
        if (href.includes('/employer/jobs') && currentPath.includes('/employer/jobs')) return true;
        if (href.includes('/employer/applications') && currentPath.includes('/employer/applications')) return true;
        if (href.includes('/employer/analytics') && currentPath.includes('/employer/analytics')) return true;
        if (href.includes('/employer/profile') && currentPath.includes('/employer/profile')) return true;
        
        return false;
    }
    
    updateScreenSize() {
        const width = window.innerWidth;
        
        this.isMobile = width <= this.breakpoints.mobile;
        this.isTablet = width > this.breakpoints.mobile && width <= this.breakpoints.tablet;
        this.isDesktop = width >= this.breakpoints.desktop;
        
        // Update body classes
        document.body.classList.toggle('mobile-layout', this.isMobile);
        document.body.classList.toggle('tablet-layout', this.isTablet);
        document.body.classList.toggle('desktop-layout', this.isDesktop);
    }
    
    setupEventListeners() {
        // Collapse button click
        if (this.collapseBtn) {
            this.collapseBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleCollapse();
            });
            
            // Keyboard support
            this.collapseBtn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleCollapse();
                }
            });
        }
        
        // Mobile toggle
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
        
        // Window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.handleResize();
            }, 150);
        });
        
        // Escape key to close mobile sidebar
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobileSidebarOpen()) {
                this.closeMobileSidebar();
            }
        });
        
        // Click outside to close mobile sidebar
        document.addEventListener('click', (e) => {
            if (this.isMobile && 
                this.isMobileSidebarOpen() && 
                !this.sidebar?.contains(e.target) && 
                !this.mobileToggle?.contains(e.target)) {
                this.closeMobileSidebar();
            }
        });
    }
    
    setupKeyboardNavigation() {
        // Add keyboard navigation for sidebar links
        const navLinks = this.sidebar?.querySelectorAll('.nav-link');
        
        navLinks?.forEach((link, index) => {
            link.addEventListener('keydown', (e) => {
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        const nextLink = navLinks[index + 1];
                        if (nextLink) nextLink.focus();
                        break;
                        
                    case 'ArrowUp':
                        e.preventDefault();
                        const prevLink = navLinks[index - 1];
                        if (prevLink) prevLink.focus();
                        break;
                        
                    case 'Home':
                        e.preventDefault();
                        navLinks[0]?.focus();
                        break;
                        
                    case 'End':
                        e.preventDefault();
                        navLinks[navLinks.length - 1]?.focus();
                        break;
                }
            });
        });
    }
    
    setupTooltips() {
        if (this.collapseBtn) {
            this.collapseBtn.setAttribute('data-tooltip', 'Toggle sidebar');
        }
        
        if (this.mobileToggle) {
            this.mobileToggle.setAttribute('title', 'Open navigation menu');
        }
    }
    
    addPulseForNewUsers() {
        const hasSeenCollapse = localStorage.getItem('employer-seen-collapse') === 'true';
        
        if (!hasSeenCollapse && this.collapseBtn && this.isDesktop) {
            // Add no-animate class first to prevent conflicts
            this.collapseBtn.classList.add('no-animate');
            
            // Small delay to ensure no conflicts
            setTimeout(() => {
                this.collapseBtn.classList.remove('no-animate');
                this.collapseBtn.classList.add('pulse');
                
                const removePulse = () => {
                    this.collapseBtn?.classList.remove('pulse');
                    this.collapseBtn?.classList.add('no-animate');
                    localStorage.setItem('employer-seen-collapse', 'true');
                    this.collapseBtn?.removeEventListener('click', removePulse);
                    
                    // Remove no-animate after a short delay
                    setTimeout(() => {
                        this.collapseBtn?.classList.remove('no-animate');
                    }, 100);
                };
                
                this.collapseBtn.addEventListener('click', removePulse, { once: true });
                
                // Auto-remove pulse after 6 seconds (3 cycles)
                setTimeout(() => {
                    if (this.collapseBtn?.classList.contains('pulse')) {
                        removePulse();
                    }
                }, 6000);
            }, 500);
        }
    }
    
    toggleCollapse() {
        if (this.isMobile) return; // Don't allow collapse on mobile
        
        this.isCollapsed = !this.isCollapsed;
        this.updateCollapseState();
        this.saveState();
        
        // Provide haptic feedback if available
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    }
    
    updateCollapseState() {
        if (!this.sidebar || !this.mainContent || !this.collapseBtn) return;
        
        // Add transitioning state to prevent blinking
        this.collapseBtn.classList.add('transitioning');
        
        // Update ARIA attributes
        this.collapseBtn.setAttribute('aria-expanded', (!this.isCollapsed).toString());
        
        // Calculate dimensions based on screen size
        const collapsedWidth = this.isTablet ? '70px' : '80px';
        const expandedWidth = this.isTablet ? '240px' : '280px';
        const collapseButtonLeft = this.isTablet ? 
            (this.isCollapsed ? '50px' : '220px') : 
            (this.isCollapsed ? '60px' : '260px');
        
        // Apply smooth transitions
        this.sidebar.style.transition = 'width 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        this.mainContent.style.transition = 'margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        this.collapseBtn.style.transition = 'left 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        
        if (this.isCollapsed) {
            // Collapse sidebar
            document.body.classList.add('sidebar-collapsed');
            this.sidebar.style.width = collapsedWidth;
            this.mainContent.style.marginLeft = collapsedWidth;
            this.collapseBtn.style.left = collapseButtonLeft;
            
            // Update tooltip
            this.collapseBtn.setAttribute('data-tooltip', 'Expand sidebar');
            
            // Enable tooltips for nav items
            this.enableNavTooltips();
        } else {
            // Expand sidebar
            document.body.classList.remove('sidebar-collapsed');
            this.sidebar.style.width = expandedWidth;
            this.mainContent.style.marginLeft = expandedWidth;
            this.collapseBtn.style.left = collapseButtonLeft;
            
            // Update tooltip
            this.collapseBtn.setAttribute('data-tooltip', 'Collapse sidebar');
            
            // Disable tooltips for nav items
            this.disableNavTooltips();
        }
        
        // Smooth visual feedback without conflicts
        requestAnimationFrame(() => {
            this.collapseBtn.style.transform = 'translateY(-50%) scale(1.05)';
            
            setTimeout(() => {
                this.collapseBtn.style.transform = 'translateY(-50%) scale(1)';
                this.collapseBtn.classList.remove('transitioning');
                
                // Trigger resize event for other components
                window.dispatchEvent(new Event('resize'));
            }, 150);
        });
    }
    
    enableNavTooltips() {
        const navLinks = this.sidebar?.querySelectorAll('.nav-link');
        
        navLinks?.forEach(link => {
            const textSpan = link.querySelector('span');
            if (textSpan) {
                const tooltipText = textSpan.textContent.trim();
                link.setAttribute('title', tooltipText);
                link.setAttribute('data-bs-toggle', 'tooltip');
                link.setAttribute('data-bs-placement', 'right');
            }
        });
        
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            navLinks?.forEach(link => {
                if (link.hasAttribute('data-bs-toggle')) {
                    new bootstrap.Tooltip(link);
                }
            });
        }
    }
    
    disableNavTooltips() {
        const navLinks = this.sidebar?.querySelectorAll('.nav-link');
        
        navLinks?.forEach(link => {
            link.removeAttribute('title');
            link.removeAttribute('data-bs-toggle');
            link.removeAttribute('data-bs-placement');
            
            // Dispose Bootstrap tooltips if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltip = bootstrap.Tooltip.getInstance(link);
                if (tooltip) {
                    tooltip.dispose();
                }
            }
        });
    }
    
    toggleMobileSidebar() {
        if (!this.isMobile) return;
        
        const isOpen = this.isMobileSidebarOpen();
        
        if (isOpen) {
            this.closeMobileSidebar();
        } else {
            this.openMobileSidebar();
        }
    }
    
    openMobileSidebar() {
        if (!this.sidebar || !this.sidebarOverlay) return;
        
        this.sidebar.classList.add('show');
        this.sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Update mobile toggle icon
        const icon = this.mobileToggle?.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-times';
        }
        
        // Focus management for accessibility
        const firstNavLink = this.sidebar.querySelector('.nav-link');
        if (firstNavLink) {
            setTimeout(() => firstNavLink.focus(), 100);
        }
        
        // Update ARIA attributes
        this.mobileToggle?.setAttribute('aria-expanded', 'true');
        this.sidebar.setAttribute('aria-hidden', 'false');
    }
    
    closeMobileSidebar() {
        if (!this.sidebar || !this.sidebarOverlay) return;
        
        this.sidebar.classList.remove('show');
        this.sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
        
        // Update mobile toggle icon
        const icon = this.mobileToggle?.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-bars';
        }
        
        // Return focus to toggle button
        if (this.mobileToggle) {
            this.mobileToggle.focus();
        }
        
        // Update ARIA attributes
        this.mobileToggle?.setAttribute('aria-expanded', 'false');
        this.sidebar.setAttribute('aria-hidden', 'true');
    }
    
    isMobileSidebarOpen() {
        return this.sidebar?.classList.contains('show') || false;
    }
    
    handleResize() {
        const wasDesktop = this.isDesktop;
        const wasMobile = this.isMobile;
        
        this.updateScreenSize();
        
        // Handle screen size changes
        if (wasMobile && !this.isMobile) {
            // Switching from mobile to larger screen
            this.closeMobileSidebar();
            this.restoreState();
        } else if (!wasMobile && this.isMobile) {
            // Switching to mobile
            if (this.isCollapsed) {
                this.isCollapsed = false;
                document.body.classList.remove('sidebar-collapsed');
            }
        }
        
        // Update button visibility
        this.updateButtonVisibility();
        
        // Update collapse state for current screen size
        if (!this.isMobile) {
            this.updateCollapseState();
        }
    }
    
    updateButtonVisibility() {
        if (this.collapseBtn) {
            this.collapseBtn.style.display = this.isMobile ? 'none' : 'flex';
        }
        
        if (this.mobileToggle) {
            this.mobileToggle.style.display = this.isMobile ? 'flex' : 'none';
        }
    }
    
    saveState() {
        localStorage.setItem('employer-sidebar-collapsed', this.isCollapsed.toString());
    }
    
    restoreState() {
        if (this.isMobile) return;
        
        const savedState = localStorage.getItem('employer-sidebar-collapsed');
        this.isCollapsed = savedState === 'true';
        
        if (this.isCollapsed) {
            this.updateCollapseState();
        }
    }
    
    // Public API methods
    collapse() {
        if (!this.isMobile && !this.isCollapsed) {
            this.toggleCollapse();
        }
    }
    
    expand() {
        if (!this.isMobile && this.isCollapsed) {
            this.toggleCollapse();
        }
    }
    
    isOpen() {
        return this.isMobile ? this.isMobileSidebarOpen() : !this.isCollapsed;
    }
    
    getState() {
        return {
            isCollapsed: this.isCollapsed,
            isMobile: this.isMobile,
            isTablet: this.isTablet,
            isDesktop: this.isDesktop,
            isMobileSidebarOpen: this.isMobileSidebarOpen()
        };
    }
}

// Initialize when DOM is loaded - prevent multiple instances
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if not already initialized
    if (!window.responsiveSidebarCollapse) {
        window.responsiveSidebarCollapse = new ResponsiveSidebarCollapse();
    }
});

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && window.responsiveSidebarCollapse) {
        // Refresh state when page becomes visible
        window.responsiveSidebarCollapse.handleResize();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ResponsiveSidebarCollapse;
}