/**
 * CSRF Token Handler for TugmaJobs
 * Ensures all AJAX requests include the CSRF token
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get the CSRF token from the meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Set up CSRF token for all AJAX requests using jQuery
    if (window.jQuery) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    }
    
    // Set up CSRF token for all fetch requests
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        // Only add CSRF token for same-origin requests
        if (url.toString().startsWith(window.location.origin) || url.toString().startsWith('/')) {
            options.headers = options.headers || {};
            
            // Don't override if already set
            if (!options.headers['X-CSRF-TOKEN'] && !options.headers['x-csrf-token']) {
                options.headers['X-CSRF-TOKEN'] = csrfToken;
            }
            
            // For form submissions
            if (options.method && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(options.method.toUpperCase())) {
                if (options.body instanceof FormData) {
                    // Add CSRF token to FormData if not already present
                    if (!options.body.has('_token')) {
                        options.body.append('_token', csrfToken);
                    }
                } else if (typeof options.body === 'string' && options.headers['Content-Type'] === 'application/x-www-form-urlencoded') {
                    // Add CSRF token to form-urlencoded data if not already present
                    if (!options.body.includes('_token=')) {
                        options.body += (options.body ? '&' : '') + '_token=' + encodeURIComponent(csrfToken);
                    }
                }
            }
        }
        
        return originalFetch(url, options);
    };
    
    // Add CSRF token to all forms
    document.querySelectorAll('form').forEach(form => {
        // Skip forms that already have a CSRF token
        if (!form.querySelector('input[name="_token"]')) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
    });
    
    // Add CSRF token to dynamically created forms
    const originalCreateElement = document.createElement;
    document.createElement = function(tagName) {
        const element = originalCreateElement.call(document, tagName);
        
        if (tagName.toLowerCase() === 'form') {
            // Add a mutation observer to add CSRF token when the form is added to the DOM
            const observer = new MutationObserver((mutations, obs) => {
                if (document.contains(element)) {
                    if (!element.querySelector('input[name="_token"]')) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        element.appendChild(csrfInput);
                    }
                    obs.disconnect(); // Stop observing once the form is in the DOM
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        
        return element;
    };
    
    // Log that CSRF protection is active
    console.log('CSRF token protection initialized');
});