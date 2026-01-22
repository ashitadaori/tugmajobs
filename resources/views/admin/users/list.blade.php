@extends('layouts.admin')

@section('page_title', 'User Management')

@section('content')
    <div class="container-fluid p-0">

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stats-card">
                    <h6 class="card-title">Total Users</h6>
                    <h3 class="mb-0">{{ $counts['total'] }}</h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h6 class="card-title">Verified</h6>
                    <h3 class="mb-0">{{ $counts['verified'] }}</h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h6 class="card-title">Unverified</h6>
                    <h3 class="mb-0">{{ $counts['unverified'] }}</h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h6 class="card-title">Employers</h6>
                    <h3 class="mb-0">{{ $counts['employers'] }}</h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h6 class="card-title">Job Seekers</h6>
                    <h3 class="mb-0">{{ $counts['jobseekers'] }}</h3>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h6 class="card-title">Admins</h6>
                    <h3 class="mb-0">{{ $counts['admins'] }}</h3>
                </div>
            </div>
        </div>

        <!-- KYC Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stats-card bg-success text-white">
                    <h6 class="card-title">KYC Verified</h6>
                    <h3 class="mb-0">{{ $counts['kyc_verified'] }}</h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card bg-warning text-white">
                    <h6 class="card-title">KYC Pending</h6>
                    <h3 class="mb-0">{{ $counts['kyc_pending'] }}</h3>
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
                <div class="row g-3 align-items-center mb-3">
                    <!-- Live Search -->
                    <div class="col-md-4">
                        <div class="position-relative">
                            <input type="text" id="live-search" class="form-control" placeholder="Search users..."
                                style="padding-left: 35px;" value="{{ request('search') }}" autocomplete="off">
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
                    <div class="col-md-2">
                        <select id="role-filter" class="form-select">
                            <option value="">All Roles</option>
                            <option value="jobseeker" {{ request('role') === 'jobseeker' ? 'selected' : '' }}>Job Seeker
                            </option>
                            <option value="employer" {{ request('role') === 'employer' ? 'selected' : '' }}>Employer</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <!-- KYC Status Filter -->
                    <div class="col-md-2">
                        <select id="kyc-filter" class="form-select">
                            <option value="all">All KYC Status</option>
                            <option value="verified" {{ request('kyc_status') === 'verified' ? 'selected' : '' }}>KYC Verified
                            </option>
                            <option value="in_progress" {{ request('kyc_status') === 'in_progress' ? 'selected' : '' }}>KYC
                                Pending</option>
                            <option value="rejected" {{ request('kyc_status') === 'rejected' ? 'selected' : '' }}>KYC Rejected
                            </option>
                        </select>
                    </div>

                    <!-- Results Info -->
                    <div class="col-md-4 text-end">
                        <span class="text-muted" id="results-info">
                            Showing <span id="results-from">{{ $users->firstItem() ?? 0 }}</span> - <span
                                id="results-to">{{ $users->lastItem() ?? 0 }}</span> of <span
                                id="results-total">{{ $users->total() }}</span> users
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="text-decoration-none text-dark">
                                        Name
                                        @if(request('sort') === 'name')
                                            <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="text-decoration-none text-dark">
                                        Email
                                        @if(request('sort') === 'email')
                                            <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Role</th>
                                <th>KYC Status</th>
                                <th>
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="text-decoration-none text-dark">
                                        Registered
                                        @if(request('sort') === 'created_at' || !request('sort'))
                                            <i
                                                class="bi bi-sort-{{ request('direction', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Actions</th>
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