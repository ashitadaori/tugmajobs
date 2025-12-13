/**
 * Jobseeker Sidebar Functionality - Consistent behavior across all jobseeker pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    function toggleMobileSidebar() {
        const sidebar = document.querySelector('.jobseeker-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const body = document.body;
        
        if (sidebar && overlay) {
            const isOpen = sidebar.classList.contains('mobile-open');
            
            if (isOpen) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
                body.style.overflow = '';
            } else {
                sidebar.classList.add('mobile-open');
                overlay.classList.add('active');
                body.style.overflow = 'hidden';
            }
        }
    }

    function closeMobileSidebar() {
        const sidebar = document.querySelector('.jobseeker-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const body = document.body;
        
        if (sidebar && overlay) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            body.style.overflow = '';
        }
    }

    // Set up event listeners
    const sidebarToggle = document.querySelector('.mobile-sidebar-toggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleMobileSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            closeMobileSidebar();
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.jobseeker-sidebar');
        const toggle = document.querySelector('.mobile-sidebar-toggle');
        
        if (window.innerWidth <= 1024 && sidebar && toggle) {
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                closeMobileSidebar();
            }
        }
    });

    // Desktop sidebar collapse functionality
    function toggleSidebarCollapse() {
        const sidebar = document.querySelector('.jobseeker-sidebar');
        const mainContent = document.querySelector('.jobseeker-main-content');
        const collapseBtn = document.querySelector('.sidebar-collapse-btn');
        const body = document.body;
        
        if (sidebar && mainContent && collapseBtn) {
            const isCollapsed = sidebar.classList.contains('collapsed');
            
            if (isCollapsed) {
                // Expand sidebar
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                collapseBtn.classList.remove('collapsed');
                body.classList.remove('sidebar-collapsed');
                // Let CSS handle the positioning with transitions
            } else {
                // Collapse sidebar
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                collapseBtn.classList.add('collapsed');
                body.classList.add('sidebar-collapsed');
                // Let CSS handle the positioning with transitions
            }
        }
    }

    // Set up collapse button
    const collapseBtn = document.querySelector('.sidebar-collapse-btn');
    if (collapseBtn) {
        collapseBtn.addEventListener('click', toggleSidebarCollapse);
    }

    // Add collapsible functionality to sidebar sections
    const sectionHeaders = document.querySelectorAll('.sidebar-section-header');
    sectionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const section = this.closest('.sidebar-section');
            section.classList.toggle('collapsed');
        });
    });

    // Highlight active menu item based on current URL
    function highlightActiveMenuItem() {
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('.jobseeker-sidebar .nav-link');
        
        sidebarLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href)) {
                link.classList.add('active');
            }
        });
    }
    
    highlightActiveMenuItem();

    // Add smooth animations on load
    function animateElements() {
        const cards = document.querySelectorAll('.modern-stats-card, .modern-content-card, .job-card-modern');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }
    
    animateElements();

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Enhanced tooltips for sidebar items
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltipElements.forEach(element => {
            new bootstrap.Tooltip(element);
        });
    }
});
