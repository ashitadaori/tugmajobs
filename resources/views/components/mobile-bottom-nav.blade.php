{{-- Mobile Bottom Navigation Component --}}
{{-- Usage: @include('components.mobile-bottom-nav') --}}

@auth
    <nav class="mobile-bottom-nav" id="mobileBottomNav" aria-label="Mobile navigation">
        @if(Auth::user()->role === 'jobseeker')
            {{-- Jobseeker Navigation --}}
            <a href="{{ route('home') }}" class="mobile-nav-item {{ request()->routeIs('home') ? 'active' : '' }}"
                aria-label="Home">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('jobs') }}"
                class="mobile-nav-item {{ request()->routeIs('jobs') || request()->routeIs('jobs.*') ? 'active' : '' }}"
                aria-label="Search Jobs">
                <i class="fas fa-search"></i>
                <span>Jobs</span>
            </a>
            <a href="{{ route('account.bookmarked-jobs.index') }}"
                class="mobile-nav-item {{ request()->routeIs('account.bookmarked-jobs.*') ? 'active' : '' }}"
                aria-label="Saved Jobs">
                <i class="fas fa-bookmark"></i>
                <span>Saved</span>
                @php $savedCount = Auth::user()->savedJobs()->count(); @endphp
                @if($savedCount > 0)
                    <span class="mobile-nav-badge">{{ $savedCount > 9 ? '9+' : $savedCount }}</span>
                @endif
            </a>
            <a href="{{ route('account.myJobApplications') }}"
                class="mobile-nav-item {{ request()->routeIs('account.myJobApplications') ? 'active' : '' }}"
                aria-label="My Applications">
                <i class="fas fa-file-alt"></i>
                <span>Applications</span>
            </a>
            <a href="{{ route('account.myProfile') }}"
                class="mobile-nav-item {{ request()->routeIs('account.myProfile') ? 'active' : '' }}" aria-label="My Profile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>

        @elseif(Auth::user()->role === 'employer')
            {{-- Employer Navigation --}}
            <a href="{{ route('employer.dashboard') }}"
                class="mobile-nav-item {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}" aria-label="Dashboard">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('employer.jobs.index') }}"
                class="mobile-nav-item {{ request()->routeIs('employer.jobs.*') ? 'active' : '' }}" aria-label="My Jobs">
                <i class="fas fa-briefcase"></i>
                <span>Jobs</span>
            </a>
            <a href="{{ route('employer.jobs.create') }}" class="mobile-nav-item mobile-nav-item-primary" aria-label="Post Job">
                <i class="fas fa-plus-circle"></i>
                <span>Post</span>
            </a>
            <a href="{{ route('employer.applications.index') }}"
                class="mobile-nav-item {{ request()->routeIs('employer.applications.*') ? 'active' : '' }}"
                aria-label="Applications">
                <i class="fas fa-users"></i>
                <span>Applicants</span>
            </a>
            <a href="{{ route('employer.profile') }}"
                class="mobile-nav-item {{ request()->routeIs('employer.profile') ? 'active' : '' }}" aria-label="Profile">
                <i class="fas fa-building"></i>
                <span>Profile</span>
            </a>
        @endif
    </nav>
@endauth

<style>
    .mobile-bottom-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--color-white, #ffffff);
        border-top: 1px solid var(--color-border, #e5e7eb);
        padding: 0.5rem 0;
        padding-bottom: calc(0.5rem + env(safe-area-inset-bottom, 0));
        z-index: 1050;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
    }

    .mobile-bottom-nav {
        display: none;
    }

    @media (max-width: 768px) {
        .mobile-bottom-nav {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        /* Add padding to main content to prevent overlap */
        body.has-mobile-nav .js-content,
        body.has-mobile-nav .ep-content,
        body.has-mobile-nav main {
            padding-bottom: 80px !important;
        }
    }

    .mobile-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        color: var(--color-text-tertiary, #6b7280);
        text-decoration: none;
        font-size: 0.65rem;
        font-weight: 500;
        min-width: 60px;
        min-height: 56px;
        position: relative;
        transition: color 0.2s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .mobile-nav-item i {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
        transition: transform 0.2s ease;
    }

    .mobile-nav-item:hover,
    .mobile-nav-item:active {
        color: var(--color-primary, #4f46e5);
    }

    .mobile-nav-item:active i {
        transform: scale(0.9);
    }

    .mobile-nav-item.active {
        color: var(--color-primary, #4f46e5);
    }

    .mobile-nav-item.active i {
        transform: scale(1.1);
    }

    .mobile-nav-item.active::after {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 3px;
        background: var(--color-primary, #4f46e5);
        border-radius: 0 0 3px 3px;
    }

    /* Primary action (Post Job) */
    .mobile-nav-item-primary {
        color: var(--color-white, #ffffff);
    }

    .mobile-nav-item-primary i {
        width: 44px;
        height: 44px;
        background: var(--color-primary, #4f46e5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.25rem;
        margin-top: -10px;
        font-size: 1.125rem;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
    }

    .mobile-nav-item-primary span {
        color: var(--color-text-secondary, #4b5563);
    }

    /* Badge */
    .mobile-nav-badge {
        position: absolute;
        top: 0.25rem;
        right: 50%;
        transform: translateX(100%);
        background: var(--color-danger, #dc2626);
        color: var(--color-white, #ffffff);
        font-size: 0.6rem;
        font-weight: 700;
        padding: 0.125rem 0.375rem;
        border-radius: 9999px;
        min-width: 16px;
        text-align: center;
    }

</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Add class to body when mobile nav is present
        const mobileNav = document.getElementById('mobileBottomNav');
        if (mobileNav && window.innerWidth <= 768) {
            document.body.classList.add('has-mobile-nav');
        }

        // Update on resize
        window.addEventListener('resize', function () {
            if (mobileNav) {
                if (window.innerWidth <= 768) {
                    document.body.classList.add('has-mobile-nav');
                } else {
                    document.body.classList.remove('has-mobile-nav');
                }
            }
        });

        // Hide mobile nav on scroll down, show on scroll up
        let lastScrollTop = 0;
        const scrollThreshold = 50;

        window.addEventListener('scroll', function () {
            if (!mobileNav) return;

            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {
                // Scrolling down
                mobileNav.style.transform = 'translateY(100%)';
            } else {
                // Scrolling up
                mobileNav.style.transform = 'translateY(0)';
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, { passive: true });
    });
</script>