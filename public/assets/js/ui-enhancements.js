/**
 * UI Enhancements for TugmaJobs
 * Improves readability and user experience
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
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
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
        });
        
        // Increase contrast on hover
        input.addEventListener('mouseover', function() {
            this.classList.add('input-hover');
        });
        
        input.addEventListener('mouseout', function() {
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
        button.addEventListener('mouseover', function() {
            this.classList.add('btn-hover-effect');
        });
        
        button.addEventListener('mouseout', function() {
            this.classList.remove('btn-hover-effect');
        });
    });

    // Add contrast to navigation on scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

    // Enhance search form with auto-focus
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        const firstInput = searchForm.querySelector('input');
        if (firstInput && window.innerWidth > 768) {
            setTimeout(() => {
                firstInput.focus();
            }, 1000);
        }
    }

    // Accessibility widget removed as per user request
});