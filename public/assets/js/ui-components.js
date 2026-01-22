/**
 * TugmaJobs - UI Components JavaScript
 * Toast notifications, loading states, and other UI interactions
 */

(function() {
    'use strict';

    // ============================================
    // TOAST NOTIFICATION SYSTEM
    // ============================================
    class ToastManager {
        constructor() {
            this.container = null;
            this.toasts = [];
            this.init();
        }

        init() {
            // Create toast container if it doesn't exist
            if (!document.querySelector('.toast-container')) {
                this.container = document.createElement('div');
                this.container.className = 'toast-container';
                this.container.setAttribute('aria-live', 'polite');
                this.container.setAttribute('aria-atomic', 'true');
                document.body.appendChild(this.container);
            } else {
                this.container = document.querySelector('.toast-container');
            }
        }

        show(options = {}) {
            const defaults = {
                type: 'info', // success, error, warning, info
                title: '',
                message: '',
                duration: 5000, // 0 for persistent
                showProgress: true,
                closable: true
            };

            const settings = { ...defaults, ...options };

            const toast = document.createElement('div');
            toast.className = `toast toast-${settings.type}`;
            toast.setAttribute('role', 'alert');

            const iconMap = {
                success: 'fas fa-check',
                error: 'fas fa-times',
                warning: 'fas fa-exclamation',
                info: 'fas fa-info'
            };

            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="${iconMap[settings.type]}"></i>
                </div>
                <div class="toast-content">
                    ${settings.title ? `<div class="toast-title">${settings.title}</div>` : ''}
                    <div class="toast-message">${settings.message}</div>
                </div>
                ${settings.closable ? `
                    <button class="toast-close" aria-label="Close notification">
                        <i class="fas fa-times"></i>
                    </button>
                ` : ''}
                ${settings.showProgress && settings.duration > 0 ? '<div class="toast-progress"></div>' : ''}
            `;

            // Add close functionality
            if (settings.closable) {
                const closeBtn = toast.querySelector('.toast-close');
                closeBtn.addEventListener('click', () => this.hide(toast));
            }

            // Set progress bar duration
            if (settings.showProgress && settings.duration > 0) {
                const progress = toast.querySelector('.toast-progress');
                progress.style.animationDuration = `${settings.duration}ms`;
            }

            // Add to container
            this.container.appendChild(toast);
            this.toasts.push(toast);

            // Auto-hide after duration
            if (settings.duration > 0) {
                setTimeout(() => this.hide(toast), settings.duration);
            }

            return toast;
        }

        hide(toast) {
            if (!toast || !toast.parentNode) return;

            toast.classList.add('hiding');

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
                this.toasts = this.toasts.filter(t => t !== toast);
            }, 200);
        }

        success(message, title = 'Success') {
            return this.show({ type: 'success', title, message });
        }

        error(message, title = 'Error') {
            return this.show({ type: 'error', title, message });
        }

        warning(message, title = 'Warning') {
            return this.show({ type: 'warning', title, message });
        }

        info(message, title = 'Info') {
            return this.show({ type: 'info', title, message });
        }

        clear() {
            this.toasts.forEach(toast => this.hide(toast));
        }
    }

    // ============================================
    // BUTTON LOADING STATES
    // ============================================
    class ButtonLoader {
        static start(button) {
            if (!button) return;

            // Store original content
            button.dataset.originalContent = button.innerHTML;
            button.dataset.originalWidth = button.offsetWidth + 'px';

            // Set fixed width to prevent layout shift
            button.style.width = button.dataset.originalWidth;

            // Add loading class
            button.classList.add('btn-loading');
            button.disabled = true;
        }

        static stop(button) {
            if (!button) return;

            // Restore original content
            if (button.dataset.originalContent) {
                button.innerHTML = button.dataset.originalContent;
            }

            // Remove loading class
            button.classList.remove('btn-loading');
            button.disabled = false;
            button.style.width = '';
        }

        static toggle(button, isLoading) {
            if (isLoading) {
                this.start(button);
            } else {
                this.stop(button);
            }
        }
    }

    // ============================================
    // SKELETON LOADER GENERATOR
    // ============================================
    class SkeletonLoader {
        static createJobCard() {
            return `
                <div class="skeleton-job-card">
                    <div class="skeleton skeleton-logo"></div>
                    <div class="skeleton-content">
                        <div class="skeleton skeleton-title"></div>
                        <div class="skeleton skeleton-text medium"></div>
                        <div class="skeleton skeleton-text short"></div>
                    </div>
                </div>
            `;
        }

        static createCard(lines = 3) {
            let linesHtml = '';
            for (let i = 0; i < lines; i++) {
                const width = i === 0 ? '' : (i % 2 === 0 ? 'short' : 'medium');
                linesHtml += `<div class="skeleton skeleton-text ${width}"></div>`;
            }

            return `
                <div class="skeleton-card">
                    <div class="skeleton skeleton-title"></div>
                    ${linesHtml}
                </div>
            `;
        }

        static show(container, count = 3, type = 'job-card') {
            if (!container) return;

            let html = '';
            for (let i = 0; i < count; i++) {
                html += type === 'job-card' ? this.createJobCard() : this.createCard();
            }

            container.innerHTML = html;
        }

        static hide(container) {
            if (!container) return;
            container.innerHTML = '';
        }
    }

    // ============================================
    // CONFIRMATION MODAL
    // ============================================
    class ConfirmModal {
        static show(options = {}) {
            const defaults = {
                type: 'warning', // danger, warning, success
                title: 'Are you sure?',
                message: 'This action cannot be undone.',
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                onConfirm: () => {},
                onCancel: () => {}
            };

            const settings = { ...defaults, ...options };

            const iconMap = {
                danger: 'fas fa-exclamation-triangle',
                warning: 'fas fa-exclamation-circle',
                success: 'fas fa-check-circle'
            };

            const btnClassMap = {
                danger: 'btn-danger',
                warning: 'btn-warning',
                success: 'btn-success'
            };

            // Create modal element
            const modalId = 'confirmModal_' + Date.now();
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content" style="border-radius: 16px; border: none;">
                            <div class="modal-body modal-confirm ${settings.type}" style="padding: 2rem;">
                                <div class="modal-icon">
                                    <i class="${iconMap[settings.type]}"></i>
                                </div>
                                <h5 class="modal-title">${settings.title}</h5>
                                <p class="modal-message">${settings.message}</p>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        ${settings.cancelText}
                                    </button>
                                    <button type="button" class="btn ${btnClassMap[settings.type]}" id="${modalId}_confirm">
                                        ${settings.confirmText}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            const modalEl = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalEl);

            // Handle confirm button
            const confirmBtn = document.getElementById(`${modalId}_confirm`);
            confirmBtn.addEventListener('click', () => {
                settings.onConfirm();
                modal.hide();
            });

            // Handle cancel
            modalEl.addEventListener('hidden.bs.modal', () => {
                settings.onCancel();
                modalEl.remove();
            });

            modal.show();

            return modal;
        }

        static danger(message, onConfirm, title = 'Delete Confirmation') {
            return this.show({
                type: 'danger',
                title,
                message,
                confirmText: 'Delete',
                onConfirm
            });
        }
    }

    // ============================================
    // FILTER CHIPS MANAGER
    // ============================================
    class FilterChips {
        constructor(container, options = {}) {
            this.container = typeof container === 'string'
                ? document.querySelector(container)
                : container;
            this.filters = new Map();
            this.onChange = options.onChange || (() => {});
            this.init();
        }

        init() {
            if (!this.container) return;
            this.container.addEventListener('click', (e) => {
                if (e.target.closest('.filter-chip-remove')) {
                    const chip = e.target.closest('.filter-chip');
                    const key = chip.dataset.filterKey;
                    this.remove(key);
                }
            });
        }

        add(key, label) {
            this.filters.set(key, label);
            this.render();
            this.onChange(this.getFilters());
        }

        remove(key) {
            this.filters.delete(key);
            this.render();
            this.onChange(this.getFilters());
        }

        clear() {
            this.filters.clear();
            this.render();
            this.onChange(this.getFilters());
        }

        getFilters() {
            return Object.fromEntries(this.filters);
        }

        render() {
            if (!this.container) return;

            if (this.filters.size === 0) {
                this.container.innerHTML = '';
                return;
            }

            let html = '';
            this.filters.forEach((label, key) => {
                html += `
                    <span class="filter-chip" data-filter-key="${key}">
                        ${label}
                        <span class="filter-chip-remove" aria-label="Remove filter">
                            <i class="fas fa-times"></i>
                        </span>
                    </span>
                `;
            });

            this.container.innerHTML = html;
        }
    }

    // ============================================
    // COLLAPSIBLE FILTER SECTIONS
    // ============================================
    class CollapsibleFilters {
        constructor(container) {
            this.container = typeof container === 'string'
                ? document.querySelector(container)
                : container;
            this.init();
        }

        init() {
            if (!this.container) return;

            const sections = this.container.querySelectorAll('.filter-section');
            sections.forEach(section => {
                const header = section.querySelector('.filter-section-header');
                if (header) {
                    header.addEventListener('click', () => {
                        section.classList.toggle('collapsed');
                    });
                }
            });
        }

        collapseAll() {
            const sections = this.container.querySelectorAll('.filter-section');
            sections.forEach(section => section.classList.add('collapsed'));
        }

        expandAll() {
            const sections = this.container.querySelectorAll('.filter-section');
            sections.forEach(section => section.classList.remove('collapsed'));
        }
    }

    // ============================================
    // INITIALIZE ON DOM READY
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize global toast manager
        window.Toast = new ToastManager();

        // Initialize button loading state helpers
        window.ButtonLoader = ButtonLoader;

        // Initialize skeleton loader helpers
        window.SkeletonLoader = SkeletonLoader;

        // Initialize confirmation modal helpers
        window.ConfirmModal = ConfirmModal;

        // Initialize filter chips manager
        window.FilterChips = FilterChips;

        // Initialize collapsible filters
        window.CollapsibleFilters = CollapsibleFilters;

        // Auto-initialize collapsible filters
        document.querySelectorAll('[data-collapsible-filters]').forEach(el => {
            new CollapsibleFilters(el);
        });

        // Auto-add loading state to forms with data-loading attribute
        document.querySelectorAll('form[data-loading]').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('[type="submit"]');
                if (submitBtn) {
                    ButtonLoader.start(submitBtn);
                }
            });
        });

        // Auto-handle delete confirmations
        document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const message = this.dataset.confirmDelete || 'Are you sure you want to delete this item?';
                const form = this.closest('form');
                const href = this.getAttribute('href');

                ConfirmModal.danger(message, () => {
                    if (form) {
                        form.submit();
                    } else if (href) {
                        window.location.href = href;
                    }
                });
            });
        });
    });

    // ============================================
    // EXPOSE FOR GLOBAL USE
    // ============================================
    window.TugmaUI = {
        ToastManager,
        ButtonLoader,
        SkeletonLoader,
        ConfirmModal,
        FilterChips,
        CollapsibleFilters
    };

})();
