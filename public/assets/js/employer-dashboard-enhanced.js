/**
 * ========================================================================
 * ENHANCED EMPLOYER DASHBOARD - DYNAMIC ANIMATIONS & INTERACTIONS
 * ========================================================================
 */

(function() {
    'use strict';

    // ===== INITIALIZATION =====
    document.addEventListener('DOMContentLoaded', function() {
        initAnimations();
        initScrollReveal();
        initCountUpAnimations();
        initTooltips();
        initRippleEffect();
        initSearchEnhancements();
        initTableEnhancements();
        initCardInteractions();
        initFormEnhancements();
        initProgressBars();
    });

    // ===== SMOOTH PAGE LOAD ANIMATIONS =====
    function initAnimations() {
        // Add stagger animation class to dashboard elements
        const statCards = document.querySelectorAll('.stat-card');
        if (statCards.length > 0) {
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        // Animate cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, (index + statCards.length) * 100);
        });
    }

    // ===== SCROLL REVEAL ANIMATION =====
    function initScrollReveal() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Add scroll-reveal class to elements
        const revealElements = document.querySelectorAll('.card, .stat-card, .table');
        revealElements.forEach(el => {
            if (!el.classList.contains('animate-fade-in')) {
                el.classList.add('scroll-reveal');
                observer.observe(el);
            }
        });
    }

    // ===== COUNT UP ANIMATION FOR NUMBERS =====
    function initCountUpAnimations() {
        const statNumbers = document.querySelectorAll('.stat-number');

        statNumbers.forEach(stat => {
            const target = parseInt(stat.textContent.replace(/,/g, ''));
            if (isNaN(target)) return;

            const duration = 2000; // 2 seconds
            const start = 0;
            const increment = target / (duration / 16); // 60fps
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                stat.textContent = Math.floor(current).toLocaleString();
            }, 16);
        });
    }

    // ===== INITIALIZE BOOTSTRAP TOOLTIPS =====
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover',
                delay: { show: 300, hide: 100 }
            });
        });
    }

    // ===== RIPPLE EFFECT ON BUTTONS =====
    function initRippleEffect() {
        const buttons = document.querySelectorAll('.btn, .nav-link, .dropdown-item');

        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple-effect');

                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add ripple CSS
        if (!document.getElementById('ripple-styles')) {
            const style = document.createElement('style');
            style.id = 'ripple-styles';
            style.textContent = `
                .btn, .nav-link, .dropdown-item {
                    position: relative;
                    overflow: hidden;
                }
                .ripple-effect {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.5);
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                }
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // ===== ENHANCED SEARCH FUNCTIONALITY =====
    function initSearchEnhancements() {
        const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');

        searchInputs.forEach(input => {
            // Add loading state
            input.addEventListener('input', debounce(function(e) {
                if (this.value.length > 2) {
                    this.classList.add('searching');
                    // Simulate search
                    setTimeout(() => {
                        this.classList.remove('searching');
                    }, 500);
                }
            }, 300));

            // Add clear button
            if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('clear-search')) {
                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'clear-search';
                clearBtn.innerHTML = '×';
                clearBtn.style.cssText = `
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    border: none;
                    background: transparent;
                    font-size: 1.5rem;
                    color: #9ca3af;
                    cursor: pointer;
                    display: none;
                `;

                input.parentElement.style.position = 'relative';
                input.parentElement.appendChild(clearBtn);

                input.addEventListener('input', function() {
                    clearBtn.style.display = this.value ? 'block' : 'none';
                });

                clearBtn.addEventListener('click', function() {
                    input.value = '';
                    input.dispatchEvent(new Event('input'));
                    this.style.display = 'none';
                    input.focus();
                });
            }
        });
    }

    // ===== TABLE ENHANCEMENTS =====
    function initTableEnhancements() {
        const tables = document.querySelectorAll('.table');

        tables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');

            // Add hover effect with slight delay
            rows.forEach((row, index) => {
                row.style.transitionDelay = `${index * 20}ms`;

                // Add click animation
                row.addEventListener('click', function(e) {
                    if (!e.target.closest('a, button')) {
                        this.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 100);
                    }
                });
            });
        });
    }

    // ===== CARD INTERACTIONS =====
    function initCardInteractions() {
        const cards = document.querySelectorAll('.card');

        cards.forEach(card => {
            // Add subtle tilt effect on mouse move
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const deltaX = (x - centerX) / centerX;
                const deltaY = (y - centerY) / centerY;

                const tiltX = deltaY * 2;
                const tiltY = deltaX * -2;

                this.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-4px)`;
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    // ===== FORM ENHANCEMENTS =====
    function initFormEnhancements() {
        const formGroups = document.querySelectorAll('.form-group, .mb-3');

        formGroups.forEach(group => {
            const input = group.querySelector('input, textarea, select');
            const label = group.querySelector('label');

            if (input && label) {
                // Floating label effect
                input.addEventListener('focus', function() {
                    label.style.transform = 'translateY(-8px) scale(0.85)';
                    label.style.color = '#667eea';
                });

                input.addEventListener('blur', function() {
                    if (!this.value) {
                        label.style.transform = '';
                        label.style.color = '';
                    }
                });

                // Check if input has value on page load
                if (input.value) {
                    label.style.transform = 'translateY(-8px) scale(0.85)';
                }
            }
        });

        // Add success/error states
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else if (this.value) {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    }

    // ===== PROGRESS BAR ANIMATIONS =====
    function initProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const targetWidth = bar.getAttribute('aria-valuenow');
                    bar.style.width = '0';
                    setTimeout(() => {
                        bar.style.transition = 'width 1s ease';
                        bar.style.width = targetWidth + '%';
                    }, 100);
                    observer.unobserve(bar);
                }
            });
        });

        progressBars.forEach(bar => observer.observe(bar));
    }

    // ===== UTILITY FUNCTIONS =====
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ===== NOTIFICATION SYSTEM =====
    window.showNotification = function(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.4s ease;
        `;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'x-circle'} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideInRight 0.4s ease reverse';
            setTimeout(() => notification.remove(), 400);
        }, 3000);
    };

    // ===== LOADING OVERLAY =====
    window.showLoadingOverlay = function(message = 'Loading...') {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        `;
        overlay.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="fw-semibold text-muted">${message}</p>
            </div>
        `;

        document.body.appendChild(overlay);
    };

    window.hideLoadingOverlay = function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 300);
        }
    };

    // ===== SMOOTH SCROLL =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ===== COPY TO CLIPBOARD =====
    window.copyToClipboard = function(text, successMessage = 'Copied to clipboard!') {
        navigator.clipboard.writeText(text).then(() => {
            showNotification(successMessage, 'success');
        }).catch(() => {
            showNotification('Failed to copy', 'danger');
        });
    };

    // ===== AUTO-SAVE INDICATOR =====
    let autoSaveTimeout;
    window.indicateAutoSave = function() {
        clearTimeout(autoSaveTimeout);

        let indicator = document.getElementById('auto-save-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'auto-save-indicator';
            indicator.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 50px;
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
                font-weight: 600;
                z-index: 9999;
                animation: slideInRight 0.4s ease;
            `;
            document.body.appendChild(indicator);
        }

        indicator.innerHTML = '<i class="bi bi-check-circle me-2"></i>Saving...';
        indicator.style.display = 'block';

        autoSaveTimeout = setTimeout(() => {
            indicator.innerHTML = '<i class="bi bi-check-circle me-2"></i>Saved';
            setTimeout(() => {
                indicator.style.animation = 'slideInRight 0.4s ease reverse';
                setTimeout(() => indicator.style.display = 'none', 400);
            }, 1500);
        }, 1000);
    };

    // Log successful initialization
    console.log('%c✨ Employer Dashboard Enhanced UI Loaded', 'color: #667eea; font-weight: bold; font-size: 14px;');
})();
