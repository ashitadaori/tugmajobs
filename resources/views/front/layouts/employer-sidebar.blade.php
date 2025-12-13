@php
    $user = auth()->user();
    $employerProfile = null;

    if ($user) {
        // Get the employer profile directly
        $employerProfile = \App\Models\Employer::where('user_id', $user->id)->first();
    }
@endphp

<script>
// AGGRESSIVE FIX - Remove ALL white backgrounds from sidebar
document.addEventListener('DOMContentLoaded', function() {
    function removeAllWhiteBackgrounds() {
        // Target ALL possible elements in sidebar
        const selectors = [
            '.employer-sidebar .nav-link',
            '.employer-sidebar a',
            '#employerSidebar .nav-link',
            '#employerSidebar a',
            '.nav.flex-column .nav-link',
            '.nav.flex-column a',
            '.sidebar .nav-link',
            '.sidebar a',
            'nav a',
            '.nav-item a',
            '.nav-item .nav-link'
        ];
        
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.style.setProperty('background', 'none', 'important');
                el.style.setProperty('background-color', 'transparent', 'important');
                el.style.setProperty('background-image', 'none', 'important');
                el.style.setProperty('box-shadow', 'none', 'important');
                el.style.setProperty('border-radius', '0', 'important');
            });
        });
    }
    
    // Run multiple times
    removeAllWhiteBackgrounds();
    setTimeout(removeAllWhiteBackgrounds, 50);
    setTimeout(removeAllWhiteBackgrounds, 100);
    setTimeout(removeAllWhiteBackgrounds, 200);
    setTimeout(removeAllWhiteBackgrounds, 500);
    setTimeout(removeAllWhiteBackgrounds, 1000);
    
    // Run on mouse events
    document.addEventListener('mouseover', function(e) {
        if (e.target.closest('.employer-sidebar, #employerSidebar')) {
            removeAllWhiteBackgrounds();
        }
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.employer-sidebar, #employerSidebar')) {
            setTimeout(removeAllWhiteBackgrounds, 10);
        }
    });
});
</script>

<!-- Unified Employer Sidebar -->
<div class="employer-sidebar" id="employerSidebar">
    <!-- User Profile Section -->
    <div class="user-profile">
        <div class="profile-container">
            @if($user->profile_photo)
                <img src="{{ asset('profile_img/' . $user->profile_photo) }}" 
                     alt="Profile Photo" 
                     class="profile-photo">
            @elseif($user->image)
                <img src="{{ asset('storage/' . $user->image) }}" 
                     alt="Profile Photo" 
                     class="profile-photo">
            @elseif($employerProfile && $employerProfile->company_logo)
                <img src="{{ $employerProfile->logo_url }}" 
                     alt="Company Logo" 
                     class="profile-photo">
            @else
                <div class="profile-initial">
                    {{ substr($employerProfile->company_name ?? $user->name, 0, 1) }}
                </div>
            @endif
            <div class="profile-info">
                <h6 class="profile-name">{{ $employerProfile->company_name ?? $user->name }}</h6>
                <small class="profile-email">{{ $user->email }}</small>
            </div>
            
            <!-- Notification Bell -->
            @include('components.notification-dropdown')
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Platform Section -->
        <div class="nav-section">
            <div class="section-label">PLATFORM</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('employer.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
                        <span class="nav-link disabled text-muted">
                            <i class="bi bi-briefcase nav-icon"></i>
                            <span class="nav-text">Jobs</span>
                            <small class="ms-auto"><i class="bi bi-tools"></i></small>
                        </span>
                    @else
                        <a href="{{ route('employer.jobs.index') }}" 
                           class="nav-link {{ request()->routeIs('employer.jobs.*') ? 'active' : '' }}">
                            <i class="bi bi-briefcase nav-icon"></i>
                            <span class="nav-text">Jobs</span>
                        </a>
                    @endif
                </li>
                <li class="nav-item">
                    @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
                        <span class="nav-link disabled text-muted">
                            <i class="bi bi-file-text nav-icon"></i>
                            <span class="nav-text">Applications</span>
                            <small class="ms-auto"><i class="bi bi-tools"></i></small>
                        </span>
                    @else
                        <a href="{{ route('employer.applications.index') }}" 
                           class="nav-link {{ request()->routeIs('employer.applications.*') ? 'active' : '' }}">
                            <i class="bi bi-file-text nav-icon"></i>
                            <span class="nav-text">Applications</span>
                        </a>
                    @endif
                </li>
                <li class="nav-item">
                    @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
                        <span class="nav-link disabled text-muted">
                            <i class="bi bi-graph-up nav-icon"></i>
                            <span class="nav-text">Analytics</span>
                            <small class="ms-auto"><i class="bi bi-tools"></i></small>
                        </span>
                    @else
                        <a href="{{ route('employer.analytics.index') }}" 
                           class="nav-link {{ request()->routeIs('employer.analytics*') ? 'active' : '' }}">
                            <i class="bi bi-graph-up nav-icon"></i>
                            <span class="nav-text">Analytics</span>
                        </a>
                    @endif
                </li>
                <li class="nav-item">
                    @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
                        <span class="nav-link disabled text-muted">
                            <i class="bi bi-building nav-icon"></i>
                            <span class="nav-text">Company Profile</span>
                            <small class="ms-auto"><i class="bi bi-tools"></i></small>
                        </span>
                    @else
                        <a href="{{ route('employer.profile.edit') }}" 
                           class="nav-link {{ request()->routeIs('employer.profile.*') ? 'active' : '' }}">
                            <i class="bi bi-building nav-icon"></i>
                            <span class="nav-text">Company Profile</span>
                        </a>
                    @endif
                </li>
                <li class="nav-item">
                    @php
                        $reviewCount = \App\Models\Review::where('employer_id', auth()->id())->count();
                        $avgRating = \App\Models\Review::getCompanyAverageRating(auth()->id());
                    @endphp
                    <a href="{{ route('employer.reviews.index') }}" 
                       class="nav-link {{ request()->routeIs('employer.reviews.*') ? 'active' : '' }}">
                        <i class="bi bi-star nav-icon"></i>
                        <span class="nav-text">Reviews</span>
                        @if($reviewCount > 0)
                            <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.7rem;">
                                {{ number_format($avgRating, 1) }} ⭐
                            </span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <!-- Settings Section -->
        <div class="nav-section">
            <div class="section-label">SETTINGS</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('employer.settings.index') }}" 
                       class="nav-link {{ request()->routeIs('employer.settings.index') ? 'active' : '' }}">
                        <i class="bi bi-gear nav-icon"></i>
                        <span class="nav-text">General</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employer.settings.notifications') }}" 
                       class="nav-link {{ request()->routeIs('employer.settings.notifications') ? 'active' : '' }}">
                        <i class="bi bi-bell nav-icon"></i>
                        <span class="nav-text">Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employer.settings.security') }}" 
                       class="nav-link {{ request()->routeIs('employer.settings.security') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock nav-icon"></i>
                        <span class="nav-text">Security</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Logout Section -->
        <div class="nav-section logout-section">
            <ul class="nav-list">
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="nav-link logout-btn">
                            <i class="bi bi-box-arrow-right nav-icon"></i>
                            <span class="nav-text">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
</div>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
/* ===== UNIFIED EMPLOYER SIDEBAR STYLES ===== */

/* Sidebar Container */
.employer-sidebar {
    background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
    width: 280px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    color: white;
    overflow-y: auto;
    padding: 0;
    box-shadow: 4px 0 20px rgba(99, 102, 241, 0.15);
    transition: transform 0.3s ease;
}

/* User Profile Section */
.user-profile {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.profile-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    position: relative;
}

/* Notification bell in sidebar */
.profile-container .nav-item.dropdown {
    position: absolute;
    top: 0;
    right: 0;
    margin: 0;
}

.profile-container .employer-notif-bell-btn {
    width: 38px;
    height: 38px;
}

.profile-container .employer-notif-bell-btn i {
    font-size: 1rem;
}

.profile-photo {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.profile-initial {
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.2);
    font-size: 1.1rem;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.profile-info {
    flex: 1;
    min-width: 0;
}

.profile-name {
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-email {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Navigation */
.sidebar-nav {
    padding: 1rem 0 0 0;
}

.nav-section {
    margin-bottom: 1.5rem;
}

.nav-section:last-child {
    margin-bottom: 0;
}

.section-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.5);
    font-weight: 700;
    letter-spacing: 1px;
    padding: 0 1.5rem;
    margin-bottom: 0.5rem;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 0.125rem;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    font-weight: 500;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.nav-icon {
    width: 18px;
    height: 18px;
    margin-right: 0.75rem;
    flex-shrink: 0;
    font-size: 1rem;
    opacity: 0.9;
}

.nav-text {
    flex: 1;
}

/* Hover Effects */
.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(2px);
}

.nav-link:hover .nav-icon {
    opacity: 1;
}

/* Active State */
.nav-link.active {
    background: transparent !important;
    color: white !important;
    font-weight: 600;
    border-right: 3px solid rgba(255, 255, 255, 0.8);
}

.nav-link.active .nav-icon {
    opacity: 1;
}

.nav-link.active:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: translateX(2px);
}

/* Logout Section */
.logout-section {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.logout-form {
    margin: 0;
    padding: 0;
}

.logout-btn {
    color: rgba(255, 255, 255, 0.7);
}

.logout-btn:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #fecaca;
}

/* Mobile Overlay */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.show {
    display: block;
    opacity: 1;
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .employer-sidebar {
        transform: translateX(-100%);
        z-index: 1050;
    }
    
    .employer-sidebar.show {
        transform: translateX(0);
    }
}

/* Scrollbar Styling */
.employer-sidebar::-webkit-scrollbar {
    width: 3px;
}

.employer-sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.employer-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
}

.employer-sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Prevent text selection on navigation */
.nav-link {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Disabled state for maintenance */
.nav-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.nav-link.disabled:hover {
    background: none;
    transform: none;
}

/* Fix for white tooltip boxes on hover */
.employer-sidebar * {
    -webkit-tap-highlight-color: transparent;
}

/* Disable browser autocomplete/autofill popups */
.employer-sidebar *,
.employer-sidebar input,
.employer-sidebar a,
.employer-sidebar button {
    -webkit-autofill: none !important;
    autocomplete: off !important;
}

/* Hide any popup/dropdown that appears on hover */
.employer-sidebar *:hover + *,
.employer-sidebar *:hover ~ * {
    display: block !important;
}

/* Remove default browser tooltips */
.employer-sidebar [title]:hover::after,
.employer-sidebar [title]:hover::before {
    display: none !important;
}

/* Hide any absolutely positioned elements that appear on hover */
.employer-sidebar .nav-item:hover > *:not(.nav-link),
.employer-sidebar .nav-link:hover > *:not(.nav-icon):not(.nav-text) {
    display: none !important;
    visibility: hidden !important;
}

/* Prevent white boxes from appearing on nav items */
.employer-sidebar .nav-link::before,
.employer-sidebar .nav-link::after,
.employer-sidebar .nav-item::before,
.employer-sidebar .nav-item::after {
    content: none !important;
    display: none !important;
}

/* Clean hover states - no tooltips */
.employer-sidebar a:hover,
.employer-sidebar button:hover {
    outline: none;
}

/* Remove any title attribute tooltips */
.employer-sidebar [title] {
    position: relative;
}

.employer-sidebar [title]:hover {
    overflow: visible;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('employerSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.querySelector('.navbar-toggler, .sidebar-toggle');
    
    // Toggle sidebar function
    function toggleSidebar() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    }
    
    // Toggle button click
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    
    // Overlay click to close
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }
    
    // Close sidebar on window resize if mobile
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991.98 && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });
    
    // Prevent sidebar close when clicking inside sidebar
    sidebar.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // AGGRESSIVE FIX: Remove all title attributes that cause white tooltips
    if (sidebar) {
        // Remove title from all elements in sidebar
        const allElements = sidebar.querySelectorAll('*');
        allElements.forEach(element => {
            element.removeAttribute('title');
            element.removeAttribute('data-title');
            element.removeAttribute('data-original-title');
        });
        
        // Prevent title attributes from being added dynamically
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && 
                    (mutation.attributeName === 'title' || 
                     mutation.attributeName === 'data-title' ||
                     mutation.attributeName === 'data-original-title')) {
                    mutation.target.removeAttribute(mutation.attributeName);
                }
            });
        });
        
        observer.observe(sidebar, {
            attributes: true,
            subtree: true,
            attributeFilter: ['title', 'data-title', 'data-original-title']
        });
    }
});
</script>
