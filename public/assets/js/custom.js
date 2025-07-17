$(document).ready(function(){
    var lazyLoadInstance = new LazyLoad({elements_selector:"img.lazy, video.lazy, div.lazy, section.lazy, header.lazy, footer.lazy,iframe.lazy"});

    // Configure toastr options
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle save/unsave job functionality
    $(document).on('click', '.save-job-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const jobId = btn.data('job-id');
        const isSaved = btn.hasClass('saved');
        
        // Disable button during request
        btn.prop('disabled', true);
        
        const url = isSaved ? `/jobs/${jobId}/unsave` : `/jobs/${jobId}/save`;
        
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                console.log('Save/unsave response:', response); // Debug log
                
                if (response.status) {
                    // Toggle saved state
                    btn.toggleClass('saved');
                    
                    // Update button text and icon
                    const icon = btn.find('i');
                    const textSpan = btn.find('span');
                    
                    if (isSaved) {
                        icon.removeClass('fas text-danger').addClass('far text-muted');
                        textSpan.text('Save Job');
                        toastr.info('Job removed from saved jobs');
                    } else {
                        icon.removeClass('far text-muted').addClass('fas text-danger');
                        textSpan.text('Saved');
                        toastr.success('Job saved successfully');
                        
                        // Play a subtle animation
                        icon.addClass('animate__animated animate__heartBeat');
                        setTimeout(() => {
                            icon.removeClass('animate__animated animate__heartBeat');
                        }, 1000);
                    }
                } else {
                    // Handle error response
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        toastr.error(response.message || 'Error saving job');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Save/unsave error:', {xhr, status, error}); // Debug log
                
                // Handle error
                const response = xhr.responseJSON;
                if (response && response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    toastr.error(response?.message || 'Error processing request');
                }
            },
            complete: function() {
                // Re-enable button after a short delay
                setTimeout(() => {
                    btn.prop('disabled', false);
                }, 500);
            }
        });
    });

    // Navigation menu functionality
    const navItems = $('.nav-item, .categories-dropdown');
    let currentDropdown = null;
    let hoverTimeout;



    // Handle hover events for desktop navigation items
    navItems.hover(
        function() {
            if (window.innerWidth >= 992) {
                clearTimeout(hoverTimeout);
                const dropdown = $(this).find('.dropdown-menu').first();
                
                // Hide previous dropdown if different
                if (currentDropdown && currentDropdown[0] !== dropdown[0]) {
                    currentDropdown.removeClass('show');
                }
                
                // Show current dropdown
                dropdown.addClass('show');
                currentDropdown = dropdown;
            }
        },
        function() {
            if (window.innerWidth >= 992) {
                const dropdown = $(this).find('.dropdown-menu').first();
                const navItem = $(this);
                
                hoverTimeout = setTimeout(() => {
                    // Only hide if we're not hovering the dropdown
                    if (!navItem.is(':hover') && !dropdown.is(':hover')) {
                        dropdown.removeClass('show');
                        if (currentDropdown && currentDropdown[0] === dropdown[0]) {
                            currentDropdown = null;
                        }
                    }
                }, 300);
            }
        }
    );

    // Handle hover for dropdown menus
    $('.dropdown-menu').hover(
        function() {
            if (window.innerWidth >= 992) {
                clearTimeout(hoverTimeout);
                $(this).addClass('show');
                currentDropdown = $(this);
            }
        },
        function() {
            if (window.innerWidth >= 992) {
                const dropdown = $(this);
                const navItem = dropdown.closest('.nav-item, .categories-dropdown');
                
                hoverTimeout = setTimeout(() => {
                    // Only hide if we're not hovering the parent nav item
                    if (!navItem.is(':hover') && !dropdown.is(':hover')) {
                        dropdown.removeClass('show');
                        if (currentDropdown && currentDropdown[0] === dropdown[0]) {
                            currentDropdown = null;
                        }
                    }
                }, 300);
            }
        }
    );

    // Handle click events for mobile navigation
    $('.nav-link').click(function(e) {
        if (window.innerWidth < 992) {
            const $navItem = $(this).parent('.nav-item');
            const hasDropdown = $navItem.find('.dropdown-menu').length > 0;
            
            if (hasDropdown) {
            e.preventDefault();
                
                // Close other dropdowns
                $('.nav-item').not($navItem).removeClass('active')
                    .find('.dropdown-menu').slideUp(300);
                
                // Toggle current dropdown
                $navItem.toggleClass('active');
                $navItem.find('.dropdown-menu').slideToggle(300);
            }
        }
    });

    // Handle click events for category dropdowns
    $('.dropdown-item-parent > a').click(function(e) {
        if (window.innerWidth < 992) {
            const hasSubmenu = $(this).siblings('.submenu').length > 0;
            if (hasSubmenu) {
                e.preventDefault();
                e.stopPropagation();
                
                const parent = $(this).parent();
                const submenu = $(this).siblings('.submenu');
                
                // Close other dropdowns at the same level
                parent.siblings().removeClass('active')
                    .find('.submenu').slideUp(300);
                parent.siblings().find('.active').removeClass('active');
                
                // Toggle current dropdown
                submenu.slideToggle(300);
                parent.toggleClass('active');
            }
        }
    });

    // Ensure menu items are clickable
    $('.dropdown-menu a, .submenu a').on('click', function(e) {
        if (window.innerWidth >= 992 || (!$(this).siblings('.submenu').length && !$(this).parent().hasClass('dropdown-item-parent'))) {
            const href = $(this).attr('href');
            if (href && href !== '#') {
                window.location.href = href;
            }
        }
    });

    // Handle menu toggle for mobile
    $('.menu-toggle').click(function() {
        $('.nav-center').toggleClass('show');
    });

    // Close mobile menu when clicking outside
    $(document).click(function(e) {
        if (window.innerWidth < 992) {
            const target = $(e.target);
            if (!target.closest('.nav-center').length && !target.closest('.menu-toggle').length) {
                $('.nav-center').removeClass('show');
                $('.dropdown-menu, .submenu').slideUp(300);
                $('.nav-item, .categories-dropdown, .dropdown-item-parent').removeClass('active');
            }
        }
    });

    // Handle window resize
    let resizeTimer;
    $(window).resize(function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth >= 992) {
                // Reset mobile menu state
                $('.nav-center').removeClass('show');
                $('.dropdown-menu, .submenu').removeAttr('style');
                $('.nav-item, .categories-dropdown, .dropdown-item-parent').removeClass('active');
            }
        }, 250);
    });
});



