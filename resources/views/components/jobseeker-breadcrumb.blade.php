<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}" class="text-decoration-none">
                <i class="bi bi-house-door me-1"></i>Home
            </a>
        </li>
        
        @if(request()->routeIs('account.dashboard'))
            <li class="breadcrumb-item active">Dashboard</li>
        @elseif(request()->routeIs('account.myJobApplications'))
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Applications</li>
        @elseif(request()->routeIs('account.saved-jobs.index'))
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Saved Jobs</li>
        @elseif(request()->routeIs('account.myProfile'))
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">My Profile</li>
        @elseif(request()->routeIs('account.resumes'))
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Resumes</li>
        @elseif(request()->routeIs('account.jobAlerts'))
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Job Alerts</li>
        @elseif(request()->routeIs('account.settings'))
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Settings</li>
        @elseif(request()->routeIs('account.changePassword'))
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Security</li>
        @else
            <li class="breadcrumb-item">
                <a href="{{ route('account.dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">{{ ucfirst(str_replace(['-', '_'], ' ', last(explode('.', request()->route()->getName())))) }}</li>
        @endif
    </ol>
</nav>

<style>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
    font-size: 0.875rem;
}

.breadcrumb-item {
    color: rgba(255, 255, 255, 0.7);
}

.breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: rgba(255, 255, 255, 0.5);
    font-weight: bold;
}

.breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #ffffff;
}
</style>
