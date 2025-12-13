// UI Consistency JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Ensure layout consistency
    ensureLayoutConsistency();
    
    // Initialize smooth animations
    initializeSmoothAnimations();
    
    // Handle responsive behavior
    handleResponsiveLayout();
    
    // Initialize accessibility features
    initializeAccessibilityFeatures();
});

function ensureLayoutConsistency() {
    const layout = document.querySelector('.employer-layout');
    const sidebar = document.querySelector('.employer-sidebar');
    const mainContent = document.querySelector('.employer-main-content');
    
    if (layout && sidebar && mainContent) {
        // Force layout styles
        layout.style.display = 'flex';
        layout.style.minHeight = 'calc(100vh - 70px)';
        layout.style.background = 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)';
        
        // Force sidebar styles
        sidebar.style.width = '280px';
        sidebar.style.minWidth = '280px';
        sidebar.style.position = 'fixed';
        sidebar.style.left = '0';
        sidebar.style.top = '70px';
        sidebar.style.height = 'calc(100vh - 70px)';
        sidebar.style.zIndex = '999';
        
        // Force main content styles
        mainContent.style.marginLeft = '280px';
        mainContent.style.width = 'calc(100% - 280px)';
        mainContent.style.padding = '2rem';
        
        // Handle mobile
        if (window.innerWidth <= 1024) {
            sidebar.style.transform = 'translateX(-100%)';
            mainContent.style.marginLeft = '0';
            mainContent.style.width = '100%';
        }
    }
}

function initializeSmoothAnimations() {
    // Add smooth transitions to all interactive elements
    const interactiveElements = document.querySelectorAll('.stat-card, .action-card, .btn');
    
    interactiveElements.forEach(element => {
        element.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    });
    
    // Animate cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    const cards = document.querySelectorAll('.stat-card, .action-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        observer.observe(card);
    });
}

function handleResponsiveLayout() {
    let resizeTimer;
    
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            ensureLayoutConsistency();
        }, 250);
    });
}

function initializeAccessibilityFeatures() {
    // Add keyboard navigation
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Add ARIA labels to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        const title = card.querySelector('.stat-title')?.textContent;
        const value = card.querySelector('.stat-value')?.textContent;
        if (title && value) {
            card.setAttribute('aria-label', `${title}: ${value}`);
        }
    });
}

// Enhanced mobile sidebar functionality
function toggleMobileSidebar() {
    const sidebar = document.querySelector('.employer-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const body = document.body;
    
    if (sidebar && overlay) {
        const isOpen = sidebar.classList.contains('mobile-open');
        
        if (isOpen) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            body.style.overflow = '';
            sidebar.style.transform = 'translateX(-100%)';
        } else {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
            body.style.overflow = 'hidden';
            sidebar.style.transform = 'translateX(0)';
        }
    }
}

function closeMobileSidebar() {
    const sidebar = document.querySelector('.employer-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const body = document.body;
    
    if (sidebar && overlay) {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        body.style.overflow = '';
        sidebar.style.transform = 'translateX(-100%)';
    }
}

// Export functions for global use
window.toggleMobileSidebar = toggleMobileSidebar;
window.closeMobileSidebar = closeMobileSidebar;

// Handle page load
window.addEventListener('load', function() {
    // Add loaded class for animations
    document.body.classList.add('page-loaded');
    
    // Ensure layout is correct after all resources load
    setTimeout(ensureLayoutConsistency, 100);
});