@php
    $user = auth()->user();
@endphp

<!-- Jobseeker Sidebar -->
<div class="jobseeker-sidebar" id="jobseekerSidebar">
    <!-- User Profile Section -->
    <div class="user-profile">
        <div class="profile-container">
            @if($user->profile_photo)
                <img src="{{ asset('profile_img/' . $user->profile_photo) }}" alt="Profile Photo" class="profile-photo">
            @elseif($user->image)
                <img src="{{ asset('storage/' . $user->image) }}" alt="Profile Photo" class="profile-photo">
            @else
                <div class="profile-initial">
                    {{ substr($user->name, 0, 1) }}
                </div>
            @endif
            <div class="profile-info">
                <h6 class="profile-name">{{ $user->name }}</h6>
                <small class="profile-email">{{ $user->email }}</small>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Navigation Section -->
        <div class="nav-section">
            <div class="section-label">NAVIGATION</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('account.dashboard') }}"
                        class="nav-link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('jobs') }}" class="nav-link {{ request()->routeIs('jobs') ? 'active' : '' }}">
                        <i class="bi bi-search nav-icon"></i>
                        <span class="nav-text">Job Search</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('companies') }}"
                        class="nav-link {{ request()->routeIs('companies*') ? 'active' : '' }}">
                        <i class="bi bi-building nav-icon"></i>
                        <span class="nav-text">Companies</span>
                        @php
                            $newCompaniesCount = \App\Models\Company::where('created_at', '>=', now()->subDays(7))->count();
                        @endphp
                        @if($newCompaniesCount > 0)
                            <span class="badge bg-success ms-2"
                                style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">{{ $newCompaniesCount }} New</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('account.myJobApplications') }}"
                        class="nav-link {{ request()->routeIs('account.myJobApplications') ? 'active' : '' }}">
                        <i class="bi bi-file-text nav-icon"></i>
                        <span class="nav-text">Applications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('account.bookmarked-jobs.index') }}"
                        class="nav-link {{ request()->routeIs('account.bookmarked-jobs.index') ? 'active' : '' }}">
                        <i class="bi bi-bookmark nav-icon"></i>
                        <span class="nav-text">Bookmarked Jobs</span>
                        @auth
                            @if(auth()->user()->role === 'jobseeker')
                                <span
                                    class="badge bg-light text-dark ms-2 bookmarked-jobs-count">{{ auth()->user()->bookmarkedJobs()->count() }}</span>
                            @endif
                        @endauth
                    </a>
                </li>
            </ul>
        </div>

        <!-- Profile Section -->
        <div class="nav-section">
            <div class="section-label">PROFILE</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('account.myProfile') }}"
                        class="nav-link {{ request()->routeIs('account.myProfile') ? 'active' : '' }}">
                        <i class="bi bi-person nav-icon"></i>
                        <span class="nav-text">Job Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('account.resume-builder.index') }}"
                        class="nav-link {{ request()->routeIs('account.resume-builder.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text nav-icon"></i>
                        <span class="nav-text">Resume Builder</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Account Section -->
        <div class="nav-section">
            <div class="section-label">ACCOUNT</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('account.settings') }}"
                        class="nav-link {{ request()->routeIs('account.settings') ? 'active' : '' }}">
                        <i class="bi bi-gear nav-icon"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('account.changePassword') }}"
                        class="nav-link {{ request()->routeIs('account.changePassword') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock nav-icon"></i>
                        <span class="nav-text">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#jobseekerHelpModal">
                        <i class="bi bi-question-circle nav-icon"></i>
                        <span class="nav-text">Help & Guide</span>
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
    /* ===== JOBSEEKER SIDEBAR STYLES ===== */

    /* Sidebar Container */
    .jobseeker-sidebar {
        background: linear-gradient(180deg, #4c1d95 0%, #7c3aed 100%);
        width: 280px;
        height: calc(100vh - 65px);
        position: fixed;
        top: 65px;
        left: 0;
        z-index: 999;
        color: white;
        overflow-y: auto;
        padding: 0;
        box-shadow: 4px 0 20px rgba(76, 29, 149, 0.15);
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

    /* Badge styling */
    .bookmarked-jobs-count {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        font-weight: 600;
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
        background: rgba(255, 255, 255, 0.15);
        color: white;
        font-weight: 600;
        border-right: 3px solid rgba(255, 255, 255, 0.8);
    }

    .nav-link.active .nav-icon {
        opacity: 1;
    }

    .nav-link.active:hover {
        background: rgba(255, 255, 255, 0.2);
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
        .jobseeker-sidebar {
            transform: translateX(-100%);
            z-index: 1050;
            top: 0;
            height: 100vh;
        }

        .jobseeker-sidebar.show {
            transform: translateX(0);
        }
    }

    /* Scrollbar Styling */
    .jobseeker-sidebar::-webkit-scrollbar {
        width: 3px;
    }

    .jobseeker-sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .jobseeker-sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
    }

    .jobseeker-sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Prevent text selection on navigation */
    .nav-link {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    /* Fix for white tooltip boxes on hover */
    .jobseeker-sidebar * {
        -webkit-tap-highlight-color: transparent;
    }

    /* Remove default browser tooltips */
    .jobseeker-sidebar [title]:hover::after,
    .jobseeker-sidebar [title]:hover::before {
        display: none !important;
    }

    /* Prevent white boxes from appearing on nav items */
    .jobseeker-sidebar .nav-link::before,
    .jobseeker-sidebar .nav-link::after,
    .jobseeker-sidebar .nav-item::before,
    .jobseeker-sidebar .nav-item::after {
        content: none !important;
        display: none !important;
    }

    /* Clean hover states - no tooltips */
    .jobseeker-sidebar a:hover,
    .jobseeker-sidebar button:hover {
        outline: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('jobseekerSidebar');
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
        window.addEventListener('resize', function () {
            if (window.innerWidth > 991.98 && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });

        // Prevent sidebar close when clicking inside sidebar
        sidebar.addEventListener('click', function (e) {
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
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
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