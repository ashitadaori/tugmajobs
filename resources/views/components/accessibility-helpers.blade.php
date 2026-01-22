{{-- Accessibility Helpers Component --}}
{{-- Include at the beginning of body for skip links --}}

{{-- Skip to Content Link --}}
<a href="#main-content" class="skip-to-content" id="skipLink">
    Skip to main content
</a>

{{-- Screen Reader Announcements --}}
<div id="a11y-announcements"
     class="sr-only"
     aria-live="polite"
     aria-atomic="true">
</div>

<style>
/* Skip to Content Link */
.skip-to-content {
    position: fixed;
    top: -100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--color-primary, #4f46e5);
    color: var(--color-white, #ffffff);
    padding: 1rem 2rem;
    border-radius: 0 0 0.5rem 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    z-index: 9999;
    transition: top 0.2s ease;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
}

.skip-to-content:focus {
    top: 0;
    outline: none;
}

/* Screen Reader Only */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Visible Focus Styles */
:focus-visible {
    outline: 3px solid rgba(79, 70, 229, 0.5);
    outline-offset: 2px;
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    :root {
        --color-primary: #0000ff;
        --color-success: #008000;
        --color-danger: #ff0000;
        --color-warning: #ffaa00;
    }

    .btn, .badge, .alert {
        border: 2px solid currentColor;
    }
}

/* Keyboard Focus Indicators */
a:focus-visible,
button:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible,
[tabindex]:focus-visible {
    outline: 3px solid rgba(79, 70, 229, 0.5);
    outline-offset: 2px;
}

/* Interactive Elements Min Touch Target */
button,
a,
input[type="submit"],
input[type="button"],
.btn,
[role="button"] {
    min-height: 44px;
    min-width: 44px;
}

/* Exception for inline links */
p a,
li a,
.inline-link {
    min-height: auto;
    min-width: auto;
}
</style>

<script>
(function() {
    'use strict';

    // Accessibility Announcer
    window.a11yAnnounce = function(message, priority = 'polite') {
        const announcer = document.getElementById('a11y-announcements');
        if (announcer) {
            announcer.setAttribute('aria-live', priority);
            announcer.textContent = '';
            // Small delay to ensure screen readers pick up the change
            setTimeout(() => {
                announcer.textContent = message;
            }, 100);
        }
    };

    // Keyboard Navigation Helper
    document.addEventListener('keydown', function(e) {
        // Escape key closes modals and dropdowns
        if (e.key === 'Escape') {
            // Close Bootstrap modals
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const modalInstance = bootstrap.Modal.getInstance(openModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            // Close Bootstrap dropdowns
            const openDropdown = document.querySelector('.dropdown-menu.show');
            if (openDropdown) {
                const toggle = openDropdown.previousElementSibling;
                if (toggle) {
                    toggle.click();
                }
            }

            // Close mobile sidebar
            const sidebar = document.querySelector('.js-sidebar.show, .ep-sidebar.show');
            if (sidebar) {
                sidebar.classList.remove('show');
                const overlay = document.querySelector('.js-sidebar-overlay.show, .ep-sidebar-overlay.show');
                if (overlay) overlay.classList.remove('show');
            }
        }

        // Tab trapping in modals
        if (e.key === 'Tab') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const focusableElements = openModal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
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
        }
    });

    // Detect keyboard vs mouse usage
    document.addEventListener('mousedown', function() {
        document.body.classList.add('using-mouse');
        document.body.classList.remove('using-keyboard');
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('using-keyboard');
            document.body.classList.remove('using-mouse');
        }
    });

    // Add aria-labels to icon-only buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Find buttons with only icons
        const iconButtons = document.querySelectorAll('button:not([aria-label]), a:not([aria-label])');
        iconButtons.forEach(btn => {
            const text = btn.textContent.trim();
            const icon = btn.querySelector('i, svg');

            // If button has no text but has an icon
            if (!text && icon) {
                // Try to derive label from icon class
                const iconClass = icon.className || '';
                let label = '';

                if (iconClass.includes('search')) label = 'Search';
                else if (iconClass.includes('close') || iconClass.includes('times')) label = 'Close';
                else if (iconClass.includes('menu') || iconClass.includes('bars')) label = 'Menu';
                else if (iconClass.includes('bell')) label = 'Notifications';
                else if (iconClass.includes('user')) label = 'Profile';
                else if (iconClass.includes('home')) label = 'Home';
                else if (iconClass.includes('bookmark')) label = 'Save';
                else if (iconClass.includes('heart')) label = 'Like';
                else if (iconClass.includes('share')) label = 'Share';
                else if (iconClass.includes('edit') || iconClass.includes('pencil')) label = 'Edit';
                else if (iconClass.includes('trash') || iconClass.includes('delete')) label = 'Delete';
                else if (iconClass.includes('plus')) label = 'Add';
                else if (iconClass.includes('arrow-left') || iconClass.includes('back')) label = 'Go back';
                else if (iconClass.includes('arrow-right') || iconClass.includes('forward')) label = 'Go forward';

                if (label) {
                    btn.setAttribute('aria-label', label);
                }
            }
        });

        // Ensure images have alt text
        const images = document.querySelectorAll('img:not([alt])');
        images.forEach(img => {
            img.setAttribute('alt', '');
            img.setAttribute('role', 'presentation');
        });

        // Add role="main" to main content if missing
        const mainContent = document.querySelector('main:not([role]), #main-content:not([role]), .main-content:not([role])');
        if (mainContent) {
            mainContent.setAttribute('role', 'main');
        }

        // Add landmark roles
        const nav = document.querySelector('nav:not([role])');
        if (nav) nav.setAttribute('role', 'navigation');

        const footer = document.querySelector('footer:not([role])');
        if (footer) footer.setAttribute('role', 'contentinfo');
    });

})();
</script>
