/**
 * UI Enhancements for TugmaJobs
 * Improves readability and user experience
 */

document.addEventListener('DOMContentLoaded', function () {
    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Enhance form controls for better readability
    document.querySelectorAll('.form-control').forEach(input => {
        // Add focus animation
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('input-focused');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('input-focused');
        });

        // Increase contrast on hover
        input.addEventListener('mouseover', function () {
            this.classList.add('input-hover');
        });

        input.addEventListener('mouseout', function () {
            this.classList.remove('input-hover');
        });
    });

    // Add animation to cards
    document.querySelectorAll('.card, .job-card, .category-card').forEach(card => {
        card.classList.add('animate-on-scroll');

        // Observe card visibility for animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        observer.observe(card);
    });

    // Enhance buttons with hover effect
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('mouseover', function () {
            this.classList.add('btn-hover-effect');
        });

        button.addEventListener('mouseout', function () {
            this.classList.remove('btn-hover-effect');
        });
    });

    // Add contrast to navigation on scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

    // Accessibility widget removed as per user request

    // Accessibility widget removed as per user request

    // ==========================================
    // NEW: UI/UX Enhancements Implementation
    // ==========================================

    // 1. Global Page Loader Logic
    const pageLoader = document.querySelector('.page-loader');

    // Show loader on page unload (navigation)
    window.addEventListener('beforeunload', function () {
        if (pageLoader) {
            pageLoader.style.width = '30%';
            setTimeout(() => pageLoader.style.width = '70%', 500);
        }
    });

    // Determine actual load progress roughly
    if (pageLoader) {
        // Initial state
        pageLoader.style.width = '30%';

        // Complete when DOM is ready
        pageLoader.style.width = '100%';
        setTimeout(() => {
            pageLoader.parentElement.style.opacity = '0';
            setTimeout(() => {
                pageLoader.style.width = '0%';
                pageLoader.parentElement.style.opacity = '1';
            }, 200);
        }, 500);
    }

    // 2. Scroll to Top Logic
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');

    if (scrollToTopBtn) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });

        scrollToTopBtn.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // 3. Intelligent Button Loading States
    // Auto-disable submit buttons on form submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            // Check if form is valid (if using browser validation)
            if (this.checkValidity()) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                    // Save original text width to prevent layout shift
                    const originalWidth = submitBtn.offsetWidth;
                    submitBtn.style.width = `${originalWidth}px`;

                    submitBtn.classList.add('btn-loading');
                    submitBtn.disabled = true;

                    // Failsafe: Re-enable after 10 seconds (in case of network error/no redirect)
                    setTimeout(() => {
                        submitBtn.classList.remove('btn-loading');
                        submitBtn.disabled = false;
                        submitBtn.style.width = '';
                    }, 10000);
                }
            }
        });
    });

    // 4. Smooth Fade-in for Main Content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.style.opacity = '0';
        mainContent.style.transition = 'opacity 0.4s ease';
        setTimeout(() => {
            mainContent.style.opacity = '1';
        }, 100);
    }
});