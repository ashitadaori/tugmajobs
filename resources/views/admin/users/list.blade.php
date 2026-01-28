@extends('layouts.admin')

@section('page_title', 'User Management')

@section('content')
    <div class="container-fluid p-0">

        <!-- Stats Cards - All in one unified row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-4 col-md-3 col-xl">
                <div class="stats-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="stats-label text-muted mb-1">Total Users</h6>
                            <h3 class="stats-value mb-0">{{ $counts['total'] }}</h3>
                        </div>
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-xl">
                <div class="stats-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="stats-label text-muted mb-1">Verified</h6>
                            <h3 class="stats-value mb-0 text-success">{{ $counts['verified'] }}</h3>
                        </div>
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-xl">
                <div class="stats-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="stats-label text-muted mb-1">Unverified</h6>
                            <h3 class="stats-value mb-0 text-warning">{{ $counts['unverified'] }}</h3>
                        </div>
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-xl">
                <div class="stats-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="stats-label text-muted mb-1">Employers</h6>
                            <h3 class="stats-value mb-0">{{ $counts['employers'] }}</h3>
                        </div>
                        <div class="stats-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-xl">
                <div class="stats-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="stats-label text-muted mb-1">Job Seekers</h6>
                            <h3 class="stats-value mb-0">{{ $counts['jobseekers'] }}</h3>
                        </div>
                        <div class="stats-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-xl">
                <div class="stats-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="stats-label text-muted mb-1">Admins</h6>
                            <h3 class="stats-value mb-0">{{ $counts['admins'] }}</h3>
                        </div>
                        <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KYC Stats Cards - Smaller, inline badges style -->
        <div class="row g-3 mb-4">
            <div class="col-auto">
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-success bg-opacity-10 border border-success border-opacity-25">
                    <i class="bi bi-patch-check-fill text-success"></i>
                    <span class="fw-semibold text-success">KYC Verified:</span>
                    <span class="badge bg-success rounded-pill">{{ $counts['kyc_verified'] }}</span>
                </div>
            </div>
            <div class="col-auto">
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-warning bg-opacity-10 border border-warning border-opacity-25">
                    <i class="bi bi-hourglass-split text-warning"></i>
                    <span class="fw-semibold text-warning">KYC Pending:</span>
                    <span class="badge bg-warning text-dark rounded-pill">{{ $counts['kyc_pending'] }}</span>
                </div>
            </div>
        </div>

        <!-- Content Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Users List</h5>
                <a href="{{ route('admin.users.export', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Export
                </a>
            </div>
            <div class="card-body">
                <!-- Search and Filters -->
                <div class="row g-3 align-items-center mb-4">
                    <!-- Live Search -->
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="position-relative">
                            <input type="text" id="live-search" class="form-control" placeholder="Search by name or email..."
                                style="padding-left: 38px;" value="{{ request('search') }}" autocomplete="off">
                            <i class="bi bi-search position-absolute"
                                style="left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                            <span id="search-spinner" class="position-absolute d-none"
                                style="right: 10px; top: 50%; transform: translateY(-50%);">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            </span>
                            <button type="button" id="clear-search" class="btn btn-link btn-sm position-absolute d-none p-0"
                                style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <select id="role-filter" class="form-select">
                            <option value="">All Roles</option>
                            <option value="jobseeker" {{ request('role') === 'jobseeker' ? 'selected' : '' }}>Job Seeker</option>
                            <option value="employer" {{ request('role') === 'employer' ? 'selected' : '' }}>Employer</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <!-- KYC Status Filter -->
                    <div class="col-6 col-md-4 col-lg-2">
                        <select id="kyc-filter" class="form-select">
                            <option value="all">All KYC Status</option>
                            <option value="verified" {{ request('kyc_status') === 'verified' ? 'selected' : '' }}>KYC Verified</option>
                            <option value="in_progress" {{ request('kyc_status') === 'in_progress' ? 'selected' : '' }}>KYC Pending</option>
                            <option value="rejected" {{ request('kyc_status') === 'rejected' ? 'selected' : '' }}>KYC Rejected</option>
                        </select>
                    </div>

                    <!-- Results Info -->
                    <div class="col-12 col-lg-4 text-lg-end">
                        <span class="text-muted" id="results-info">
                            Showing <strong id="results-from">{{ $users->firstItem() ?? 0 }}</strong> - <strong id="results-to">{{ $users->lastItem() ?? 0 }}</strong> of <strong id="results-total">{{ $users->total() }}</strong> users
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 150px;">
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                        Name
                                        @if(request('sort') === 'name')
                                            <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width: 200px;">
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                        Email
                                        @if(request('sort') === 'email')
                                            <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width: 100px;">Role</th>
                                <th style="min-width: 120px;">KYC Status</th>
                                <th style="min-width: 120px;">
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                        Registered
                                        @if(request('sort') === 'created_at' || !request('sort'))
                                            <i class="bi bi-sort-{{ request('direction', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            @include('admin.users.partials.users-table-rows', ['users' => $users])
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4" id="pagination-container">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
        <style>
            /* Stats Card Styling */
            .stats-card {
                padding: 1.25rem;
                border-radius: 12px;
                transition: all 0.2s ease;
            }

            .stats-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .stats-label {
                font-size: 0.8rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .stats-value {
                font-size: 1.75rem;
                font-weight: 700;
            }

            .stats-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
            }

            /* Table Styling */
            .table th a {
                color: inherit;
                text-decoration: none;
            }

            .table th a:hover {
                color: var(--bs-primary);
            }

            /* Search Input Enhancement */
            #live-search {
                border-radius: 8px;
                border: 1px solid var(--admin-border-color);
            }

            #live-search:focus {
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
                border-color: #6366f1;
            }

            /* Filter Selects */
            .form-select {
                border-radius: 8px;
            }

            /* Card Header */
            .card-header {
                border-radius: 12px 12px 0 0 !important;
                padding: 1rem 1.25rem;
            }

            .card {
                border-radius: 12px;
                overflow: hidden;
            }

            /* Badge Styling */
            .badge {
                font-weight: 500;
            }

            /* Role Badges */
            .badge-role-jobseeker {
                background-color: #0ea5e9;
                color: white;
            }

            .badge-role-employer {
                background-color: #10b981;
                color: white;
            }

            .badge-role-admin {
                background-color: #6366f1;
                color: white;
            }

            /* KYC Status Badges */
            .badge-kyc-verified {
                background-color: #10b981;
                color: white;
            }

            .badge-kyc-pending {
                background-color: #f59e0b;
                color: #1f2937;
            }

            .badge-kyc-rejected {
                background-color: #ef4444;
                color: white;
            }

            /* Table Row Hover */
            .table-hover tbody tr:hover {
                background-color: rgba(99, 102, 241, 0.04);
            }

            /* Dark Mode Adjustments */
            [data-theme="dark"] .stats-icon {
                opacity: 0.9;
            }

            [data-theme="dark"] .bg-opacity-10 {
                --bs-bg-opacity: 0.15 !important;
            }

            [data-theme="dark"] .border-opacity-25 {
                --bs-border-opacity: 0.3 !important;
            }

            [data-theme="dark"] .table-hover tbody tr:hover {
                background-color: rgba(99, 102, 241, 0.08);
            }

            [data-theme="dark"] .badge-kyc-pending {
                color: #111827;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script>
            $(document).ready(function () {
                // ========== LIVE SEARCH FUNCTIONALITY ==========
                const searchInput = document.getElementById('live-search');
                const roleFilter = document.getElementById('role-filter');
                const kycFilter = document.getElementById('kyc-filter');
                const tableBody = document.getElementById('users-table-body');
                const resultsFrom = document.getElementById('results-from');
                const resultsTo = document.getElementById('results-to');
                const resultsTotal = document.getElementById('results-total');
                const paginationContainer = document.getElementById('pagination-container');
                const searchSpinner = document.getElementById('search-spinner');
                const clearSearchBtn = document.getElementById('clear-search');

                let searchTimeout = null;
                let currentRequest = null;

                // Function to perform the search
                function performSearch() {
                    const query = searchInput.value.trim();
                    const role = roleFilter.value;
                    const kycStatus = kycFilter.value;

                    // Show/hide clear button
                    if (query.length > 0) {
                        clearSearchBtn.classList.remove('d-none');
                        searchSpinner.classList.add('d-none');
                    } else {
                        clearSearchBtn.classList.add('d-none');
                    }

                    // Show loading spinner
                    searchSpinner.classList.remove('d-none');
                    clearSearchBtn.classList.add('d-none');

                    // Cancel previous request if any
                    if (currentRequest) {
                        currentRequest.abort();
                    }

                    // Create new request
                    currentRequest = new AbortController();

                    // Build the URL with query parameters
                    const params = new URLSearchParams();
                    if (query) params.append('q', query);
                    if (role) params.append('role', role);
                    if (kycStatus && kycStatus !== 'all') params.append('kyc_status', kycStatus);

                    const url = '{{ route("admin.users.search") }}?' + params.toString();

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        signal: currentRequest.signal
                    })
                        .then(response => response.json())
                        .then(data => {
                            // Update table body
                            tableBody.innerHTML = data.html;

                            // Update results info
                            resultsFrom.textContent = data.from || 0;
                            resultsTo.textContent = data.to || 0;
                            resultsTotal.textContent = data.total;

                            // Update pagination
                            paginationContainer.innerHTML = data.pagination;

                            // Hide spinner, show clear button if there's text
                            searchSpinner.classList.add('d-none');
                            if (query.length > 0) {
                                clearSearchBtn.classList.remove('d-none');
                            }
                        })
                        .catch(error => {
                            if (error.name !== 'AbortError') {
                                console.error('Search error:', error);
                            }
                            searchSpinner.classList.add('d-none');
                            if (query.length > 0) {
                                clearSearchBtn.classList.remove('d-none');
                            }
                        });
                }

                // Debounced search on input
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimeout);
                    // Debounce: wait 300ms after user stops typing
                    searchTimeout = setTimeout(performSearch, 300);
                });

                // Immediate search on filter change
                roleFilter.addEventListener('change', function () {
                    clearTimeout(searchTimeout);
                    performSearch();
                });

                kycFilter.addEventListener('change', function () {
                    clearTimeout(searchTimeout);
                    performSearch();
                });

                // Clear search button
                clearSearchBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    clearSearchBtn.classList.add('d-none');
                    performSearch();
                    searchInput.focus();
                });

                // Search on Enter key
                searchInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(searchTimeout);
                        performSearch();
                    }
                });

                // Keyboard shortcut: Ctrl+K or Cmd+K to focus search
                document.addEventListener('keydown', function (e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    }

                    // Escape to clear and blur search
                    if (e.key === 'Escape' && document.activeElement === searchInput) {
                        searchInput.value = '';
                        clearSearchBtn.classList.add('d-none');
                        searchInput.blur();
                        performSearch();
                    }
                });
            });
        </script>
    @endpush
@endsection