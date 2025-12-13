/**
 * No Blink JavaScript
 * Adds "loaded" class to body after page is fully loaded
 * This re-enables smooth transitions after preventing initial load animations
 */

// Add loaded class immediately when DOM is ready (not waiting for images)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Add loaded class after a tiny delay to ensure DOM is painted
        setTimeout(function() {
            document.body.classList.add('loaded');
        }, 100);
    });
} else {
    // DOM is already ready
    document.body.classList.add('loaded');
}

// Fallback: ensure loaded class is added on window load
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});
