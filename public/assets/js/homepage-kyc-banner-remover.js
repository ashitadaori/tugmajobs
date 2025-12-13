/**
 * Homepage KYC Banner Remover
 * This script forcibly removes KYC banners from the homepage to prevent navbar alignment issues
 */

(function() {
    'use strict';
    
    // Function to check if we're on pages where KYC banner should be hidden
    function isKycBannerHiddenPage() {
        return window.location.pathname === '/' || 
               window.location.search.includes('force_home=1') ||
               window.location.href.includes('?force_home=1') ||
               window.location.pathname === '/jobs' ||
               window.location.pathname.startsWith('/jobs/') ||
               window.location.pathname === '/companies' ||
               window.location.pathname.startsWith('/companies/');
    }
    
    // Function to remove KYC banners
    function removeKycBanners() {
        if (!isKycBannerHiddenPage()) {
            return;
        }
        
        const kycBanners = document.querySelectorAll('.kyc-reminder-banner, [class*="kyc"], [id*="kyc"]');
        let bannersRemoved = 0;
        
        kycBanners.forEach(function(banner) {
            // Check if this element is actually a KYC banner by checking text content
            const text = banner.textContent.toLowerCase();
            if (text.includes('kyc') || 
                text.includes('verification') || 
                text.includes('verify') ||
                banner.classList.contains('kyc-reminder-banner')) {
                
                banner.style.display = 'none !important';
                banner.style.visibility = 'hidden !important';
                banner.style.height = '0px !important';
                banner.style.overflow = 'hidden !important';
                banner.remove();
                bannersRemoved++;
            }
        });
        
        // Add appropriate class to body based on current page
        if (window.location.pathname === '/' || window.location.search.includes('force_home=1')) {
            document.body.classList.add('homepage');
        } else if (window.location.pathname === '/jobs' || window.location.pathname.startsWith('/jobs/')) {
            document.body.classList.add('jobs-page');
        } else if (window.location.pathname === '/companies' || window.location.pathname.startsWith('/companies/')) {
            document.body.classList.add('companies-page');
        }
        document.body.classList.remove('has-kyc-banner');
        
        // Reset navbar position
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.style.top = '0px';
        }
        
        console.log(`KYC Banner Remover: Removed ${bannersRemoved} banners from ${window.location.pathname}`);
    }
    
    // Function to observe for dynamically added banners
    function observeForBanners() {
        if (!isKycBannerHiddenPage()) {
            return;
        }
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            // Check if the added node or its children contain KYC banners
                            const kycElements = node.querySelectorAll ? 
                                node.querySelectorAll('.kyc-reminder-banner, [class*="kyc"]') : 
                                [];
                            
                            if (node.classList && (node.classList.contains('kyc-reminder-banner') || 
                                node.className.toLowerCase().includes('kyc'))) {
                                node.remove();
                                console.log('KYC Banner Remover: Removed dynamically added banner');
                            }
                            
                            kycElements.forEach(function(element) {
                                element.remove();
                                console.log('KYC Banner Remover: Removed dynamically added child banner');
                            });
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Disconnect observer after 10 seconds to avoid memory leaks
        setTimeout(function() {
            observer.disconnect();
        }, 10000);
    }
    
    // Run immediately if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            removeKycBanners();
            observeForBanners();
        });
    } else {
        removeKycBanners();
        observeForBanners();
    }
    
    // Also run after a short delay to catch any delayed banner rendering
    setTimeout(function() {
        removeKycBanners();
    }, 100);
    
    // Run again after page load
    window.addEventListener('load', function() {
        removeKycBanners();
    });
    
})();
