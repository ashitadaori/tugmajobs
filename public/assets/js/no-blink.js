/**
 * No Blink JavaScript
 * Handles smooth page load transitions and navigation
 * Adds "page-loaded" class to body after content is ready
 */

(function() {
    'use strict';

    // Configuration
    var LOAD_DELAY = 50; // ms to wait before showing content
    var TRANSITION_DURATION = 150; // ms for page transition

    /**
     * Mark page as loaded - enables animations and shows content
     */
    function markPageLoaded() {
        // Remove any existing loaded class first
        document.body.classList.remove('loaded');

        // Add the page-loaded class after a small delay
        // This ensures the browser has painted the initial state
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                document.body.classList.add('page-loaded');
            });
        });
    }

    /**
     * Handle page transition when navigating away
     */
    function startPageTransition() {
        document.body.classList.add('page-transitioning');
    }

    /**
     * Initialize page load handling
     */
    function init() {
        // If DOM is still loading, wait for it
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(markPageLoaded, LOAD_DELAY);
            });
        } else {
            // DOM is already ready
            setTimeout(markPageLoaded, LOAD_DELAY);
        }

        // Fallback: ensure page-loaded class is added on window load
        window.addEventListener('load', function() {
            if (!document.body.classList.contains('page-loaded')) {
                markPageLoaded();
            }
        });

        // Handle navigation clicks for smooth page transitions
        document.addEventListener('click', function(e) {
            var link = e.target.closest('a');

            // Check if it's a navigation link
            if (link &&
                link.href &&
                !link.target &&
                !link.hasAttribute('data-no-transition') &&
                !link.classList.contains('no-loader') &&
                !e.ctrlKey &&
                !e.metaKey &&
                !e.shiftKey) {

                // Check if it's an internal link
                var url = new URL(link.href, window.location.origin);
                if (url.origin === window.location.origin &&
                    url.pathname !== window.location.pathname) {

                    // Skip transition for hash links and downloads
                    if (url.hash && url.pathname === window.location.pathname) {
                        return;
                    }
                    if (link.hasAttribute('download')) {
                        return;
                    }

                    // Start the transition
                    startPageTransition();
                }
            }
        });

        // Handle browser back/forward navigation
        window.addEventListener('pageshow', function(e) {
            // If coming from bfcache, re-mark as loaded
            if (e.persisted) {
                document.body.classList.remove('page-transitioning');
                markPageLoaded();
            }
        });

        // Handle page hide for smooth transitions
        window.addEventListener('pagehide', function() {
            // Reset state when leaving page
            document.body.classList.remove('page-loaded');
        });
    }

    // Start initialization
    init();
})();
