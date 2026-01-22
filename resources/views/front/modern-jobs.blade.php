@extends('layouts.jobseeker')

@section('page-title', 'Find Jobs')

@section('jobseeker-content')
    <div class="jobs-page-container">
        <!-- Enhanced Page Header with Integrated Search -->
        <header class="jobs-header">
            <!-- Decorative Elements -->
            <div class="header-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
                <div class="decoration-circle circle-3"></div>
                <div class="decoration-dots"></div>
                <div class="decoration-grid"></div>
            </div>

            <div class="jobs-header-inner">
                <div class="jobs-header-content">
                    <div class="header-text-center">
                        <span class="header-badge">
                            <i class="fas fa-sparkles"></i>
                            {{ $jobs->total() }} Active Opportunities
                        </span>
                        <h1 class="jobs-title">Find Your Dream Job</h1>
                        <p class="jobs-subtitle">
                            Discover amazing career opportunities that match your skills and aspirations
                        </p>
                    </div>

                    <!-- Integrated Search Box -->
                    <div class="header-search-container">
                        <form action="" method="GET" class="header-search-form" id="headerSearchForm">
                            <div class="search-input-group">
                                <div class="search-field keyword-field">
                                    <i class="fas fa-search field-icon"></i>
                                    <input type="text" name="keyword" id="headerKeyword"
                                        value="{{ Request::get('keyword') }}" placeholder="Job title, skills, or company"
                                        class="search-input">
                                </div>
                                <div class="search-divider"></div>
                                <div class="search-field location-field">
                                    <i class="fas fa-map-marker-alt field-icon"></i>
                                    <input type="text" name="location" id="headerLocation"
                                        value="{{ Request::get('location') }}" placeholder="City or province"
                                        class="search-input">
                                    <button type="button" class="location-btn" onclick="useHeaderLocation()"
                                        title="Use my location">
                                        <i class="fas fa-crosshairs"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="location_filter_latitude" id="header_location_latitude"
                                    value="{{ Request::get('location_filter_latitude') }}">
                                <input type="hidden" name="location_filter_longitude" id="header_location_longitude"
                                    value="{{ Request::get('location_filter_longitude') }}">
                                <button type="submit" class="search-submit-btn">
                                    <i class="fas fa-search"></i>
                                    <span>Search Jobs</span>
                                </button>
                            </div>
                        </form>

                        <!-- Quick Tags -->
                        <div class="quick-search-tags">
                            <span class="quick-label">Popular:</span>
                            <a href="?keyword=Developer" class="quick-tag">Developer</a>
                            <a href="?keyword=Designer" class="quick-tag">Designer</a>
                            <a href="?keyword=Marketing" class="quick-tag">Marketing</a>
                            <a href="?keyword=Sales" class="quick-tag">Sales</a>
                            <a href="?keyword=Remote" class="quick-tag">
                                <i class="fas fa-home"></i> Remote
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Pills -->
                <div class="header-stats-row">
                    <div class="stat-pill">
                        <i class="fas fa-briefcase"></i>
                        <span><strong>{{ $jobs->total() }}</strong> Jobs</span>
                    </div>
                    <div class="stat-pill">
                        <i class="fas fa-building"></i>
                        <span><strong>{{ \App\Models\User::where('role', 'employer')->count() }}</strong> Companies</span>
                    </div>
                    <div class="stat-pill">
                        <i class="fas fa-clock"></i>
                        <span><strong>{{ \App\Models\Job::where('created_at', '>=', now()->subDay())->count() }}</strong>
                            New Today</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area - Full Width -->
        <div class="jobs-content-wrapper">
            <main class="jobs-main-full">
                <!-- Smart Matching Status -->
                @if($userHasPreferences)
                    <div class="smart-match-banner active">
                        <div class="smart-match-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="smart-match-content">
                            <strong>Smart Matching Active</strong>
                            <span>Personalized results based on {{ count($userCategories) }}
                                {{ Str::plural('preference', count($userCategories)) }}</span>
                        </div>
                        <a href="{{ route('account.myProfile') }}" class="smart-match-settings" aria-label="Settings">
                            <i class="fas fa-cog"></i>
                        </a>
                    </div>
                @elseif($categoryPrompt)
                    <div class="smart-match-banner inactive">
                        <div class="smart-match-icon">
                            <i class="fas fa-magic"></i>
                        </div>
                        <div class="smart-match-content">
                            <strong>Enable Smart Matching</strong>
                            <span>Set preferences for personalized job recommendations</span>
                        </div>
                        <a href="{{ route('account.myProfile') }}" class="smart-match-activate">
                            Activate
                        </a>
                    </div>
                @endif

                <!-- Recommended Jobs Section -->
                @if($recommendedJobs->isNotEmpty())
                    <section class="recommended-jobs-section">
                        <div class="section-header">
                            <div class="section-title-group">
                                <i class="fas fa-star"></i>
                                <h2>Top Matches for You</h2>
                            </div>
                            <span class="powered-badge">AI Powered</span>
                        </div>
                        <div class="recommended-jobs-grid">
                            @foreach($recommendedJobs->take(3) as $recJob)
                                <article class="recommended-job-card">
                                    <div class="match-score">
                                        {{ 95 - ($loop->index * 5) }}% Match
                                    </div>
                                    <div class="recommended-job-content">
                                        <div class="company-initial">
                                            {{ substr($recJob->employer->employerProfileDirect->company_name ?? $recJob->employer->company_name ?? $recJob->employer->name ?? 'C', 0, 1) }}
                                        </div>
                                        <h3 class="recommended-job-title">{{ $recJob->title }}</h3>
                                        <p class="recommended-company">
                                            {{ $recJob->employer->employerProfileDirect->company_name ?? $recJob->employer->company_name ?? $recJob->employer->name ?? 'Company' }}
                                            <x-verified-badge :user="$recJob->employer" size="xs" />
                                        </p>
                                        <div class="recommended-tags">
                                            <span class="tag-type">{{ $recJob->jobType->name }}</span>
                                            <span class="tag-category">{{ $recJob->category->name }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('jobDetail', $recJob->id) }}" class="recommended-job-link">
                                        View Details
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Results Header -->
                <div class="results-header">
                    <div class="results-info">
                        <span class="results-count">
                            <strong>{{ $jobs->total() }}</strong> jobs found
                        </span>
                        @if(Request::get('keyword') || Request::get('location') || Request::get('jobType'))
                            <div class="active-filters">
                                @if(Request::get('keyword'))
                                    <span class="active-filter-tag">
                                        <i class="fas fa-search"></i>
                                        {{ Request::get('keyword') }}
                                    </span>
                                @endif
                                @if(Request::get('location'))
                                    <span class="active-filter-tag">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ Request::get('location') }}
                                    </span>
                                @endif
                                @if(Request::get('jobType'))
                                    <span class="active-filter-tag">
                                        <i class="fas fa-briefcase"></i>
                                        {{ Request::get('jobType') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="results-sort">
                        <form action="" method="GET" id="sortForm" class="sort-form">
                            @if(Request::get('keyword'))
                                <input type="hidden" name="keyword" value="{{ Request::get('keyword') }}">
                            @endif
                            @if(Request::get('location'))
                                <input type="hidden" name="location" value="{{ Request::get('location') }}">
                            @endif
                            @if(Request::get('jobType'))
                                <input type="hidden" name="jobType" value="{{ Request::get('jobType') }}">
                            @endif
                            <label for="sort">Sort:</label>
                            <select name="sort" id="sort" onchange="this.form.submit()">
                                <option value="1" {{ (Request::get('sort') != '0') ? 'selected' : '' }}>Newest</option>
                                <option value="0" {{ (Request::get('sort') == '0') ? 'selected' : '' }}>Oldest</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Job Listings - Grid Style -->
                @if ($jobs->isNotEmpty())
                    <div class="job-grid">
                        @foreach ($jobs as $job)
                            <article class="job-card-grid">
                                <!-- Card Header -->
                                <div class="job-card-header">
                                    <div class="job-card-top">
                                        <div class="job-company-logo">
                                            {{ substr($job->employer->employerProfileDirect->company_name ?? $job->employer->company_name ?? $job->employer->name ?? 'C', 0, 1) }}
                                        </div>
                                        <div class="job-header-info">
                                            <h3 class="job-title">
                                                <a href="{{ route('jobDetail', $job->id) }}">{{ $job->title }}</a>
                                            </h3>
                                            <p class="job-company">
                                                {{ $job->employer->employerProfileDirect->company_name ?? $job->employer->company_name ?? $job->employer->name ?? 'Company' }}
                                                <x-verified-badge :user="$job->employer" size="xs" />
                                            </p>
                                        </div>
                                        <button class="job-menu-btn" aria-label="Options">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </div>
                                    <div class="job-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $job->location }}
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="job-card-body">
                                    <div class="job-info-row">
                                        <span class="job-info-item">
                                            <span class="info-label">{{ $job->experience_level ?? 'Entry Level' }}</span>
                                        </span>
                                        <span class="job-info-item">
                                            <span class="info-label">{{ $job->jobType->name }}</span>
                                        </span>
                                        @if ($job->salary_range)
                                            <span class="job-info-item salary">
                                                <span class="info-label">{{ $job->salary_range }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <p class="job-description">{{ Str::words(strip_tags($job->description), 20, '...') }}</p>
                                </div>

                                <!-- Card Tags -->
                                <div class="job-card-tags">
                                    <span class="job-tag primary">{{ $job->jobType->name }}</span>
                                    <span class="job-tag">{{ Str::limit($job->category->name, 18) }}</span>
                                    @if($job->skills)
                                        @foreach(array_slice(explode(',', $job->skills), 0, 2) as $skill)
                                            <span class="job-tag">{{ trim($skill) }}</span>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Card Footer -->
                                <div class="job-card-footer">
                                    <span class="job-posted">
                                        <i class="far fa-clock"></i>
                                        {{ $job->created_at->diffForHumans() }}
                                    </span>
                                    <div class="job-actions">
                                        <a href="{{ route('jobDetail', $job->id) }}" class="job-view-btn">
                                            View Job
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                        <x-save-job-button :job="$job" size="sm" class="job-action-btn save" />
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($jobs->hasPages())
                        <div class="pagination-container">
                            {{ $jobs->withQueryString()->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @else
                    <div class="empty-state-card">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>No Jobs Found</h3>
                        <p>We couldn't find jobs matching your criteria. Try adjusting your filters.</p>
                        <a href="{{ route('jobs') }}" class="empty-reset-btn">
                            <i class="fas fa-redo"></i>
                            Reset Filters
                        </a>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <style>
        /* ============================================
       PROFESSIONAL JOBS PAGE - CLEAN & MODERN
       ============================================ */

        /* === Container === */
        .jobs-page-container {
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 0 2rem 3rem;
        }

        /* === Enhanced Header with Integrated Search === */
        .jobs-header {
            background: linear-gradient(135deg, #312e81 0%, #4338ca 30%, #6366f1 70%, #818cf8 100%);
            padding: 3.5rem 0 3rem;
            margin: -2rem -3rem 2.5rem -3rem;
            width: calc(100% + 6rem);
            position: relative;
            overflow: hidden;
            border-radius: 0 0 40px 40px;
            box-shadow: 0 20px 60px rgba(79, 70, 229, 0.25), 0 8px 24px rgba(79, 70, 229, 0.15);
        }

        /* Header Decorative Elements */
        .header-decoration {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .decoration-circle.circle-1 {
            width: 400px;
            height: 400px;
            top: -200px;
            right: -100px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 70%);
        }

        .decoration-circle.circle-2 {
            width: 300px;
            height: 300px;
            bottom: -150px;
            left: 5%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
        }

        .decoration-circle.circle-3 {
            width: 200px;
            height: 200px;
            top: 40%;
            right: 25%;
            transform: translateY(-50%);
            background: radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, transparent 70%);
        }

        .decoration-dots {
            position: absolute;
            top: 30px;
            right: 10%;
            width: 100px;
            height: 100px;
            background-image: radial-gradient(rgba(255, 255, 255, 0.25) 1px, transparent 1px);
            background-size: 12px 12px;
            opacity: 0.6;
        }

        .decoration-grid {
            position: absolute;
            bottom: 20px;
            left: 5%;
            width: 120px;
            height: 80px;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.4;
        }

        .jobs-header-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 3rem;
            position: relative;
            z-index: 1;
        }

        .jobs-header-content {
            text-align: center;
        }

        .header-text-center {
            margin-bottom: 2rem;
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 0.625rem 1.25rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 1.25rem;
            animation: pulse-glow 3s ease-in-out infinite;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            }

            50% {
                box-shadow: 0 4px 24px rgba(255, 255, 255, 0.25);
            }
        }

        .header-badge i {
            color: #fbbf24;
            font-size: 0.9rem;
            filter: drop-shadow(0 2px 4px rgba(251, 191, 36, 0.4));
        }

        .jobs-title {
            color: #fff;
            font-size: 2.75rem;
            font-weight: 800;
            margin: 0 0 1rem;
            letter-spacing: -0.03em;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            line-height: 1.15;
        }

        .jobs-subtitle {
            color: rgba(255, 255, 255, 0.92);
            font-size: 1.15rem;
            margin: 0;
            font-weight: 400;
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.55;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* === Header Search Container === */
        .header-search-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header-search-form {
            margin-bottom: 1.25rem;
        }

        .search-input-group {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 20px;
            padding: 0.625rem;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25), 0 12px 32px rgba(0, 0, 0, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }

        .search-input-group:focus-within {
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3), 0 12px 32px rgba(79, 70, 229, 0.2);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
        }

        .search-field {
            display: flex;
            align-items: center;
            flex: 1;
            padding: 0.875rem 1.25rem;
            gap: 0.875rem;
            position: relative;
        }

        .search-field .field-icon {
            color: #6366f1;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .search-field .search-input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 1rem;
            color: #1e293b;
            outline: none;
            min-width: 0;
            font-weight: 500;
        }

        .search-field .search-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .search-field .search-input:focus {
            outline: none;
        }

        .keyword-field {
            min-width: 260px;
        }

        .location-field {
            position: relative;
        }

        .location-btn {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
        }

        .location-btn:hover {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4f46e5;
            transform: translateY(-50%) scale(1.05);
        }

        .location-btn i {
            font-size: 0.9rem;
        }

        .search-divider {
            width: 2px;
            height: 36px;
            background: linear-gradient(180deg, transparent 0%, #e2e8f0 50%, transparent 100%);
            flex-shrink: 0;
            margin: 0 0.25rem;
        }

        .search-submit-btn {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 1rem 1.75rem;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
            flex-shrink: 0;
            letter-spacing: 0.3px;
        }

        .search-submit-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 28px rgba(79, 70, 229, 0.5);
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 50%, #6d28d9 100%);
        }

        .search-submit-btn:active {
            transform: translateY(-1px) scale(0.98);
        }

        .search-submit-btn i {
            font-size: 0.9rem;
        }

        /* Quick Search Tags */
        .quick-search-tags {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.625rem;
        }

        .quick-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 0.25rem;
        }

        .quick-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .quick-tag:hover {
            background: rgba(255, 255, 255, 0.35);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .quick-tag i {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        /* Header Stats Row */
        .header-stats-row {
            display: flex;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .stat-pill {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.75rem 1.25rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 50px;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .stat-pill:hover {
            background: rgba(255, 255, 255, 0.22);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .stat-pill i {
            font-size: 0.95rem;
            color: #fbbf24;
            filter: drop-shadow(0 1px 2px rgba(251, 191, 36, 0.3));
        }

        .stat-pill strong {
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
        }

        /* Header Responsive */
        @media (max-width: 900px) {
            .search-input-group {
                flex-direction: column;
                padding: 0.75rem;
                gap: 0;
            }

            .search-field {
                width: 100%;
                border-bottom: 1px solid #f1f5f9;
                padding: 0.875rem;
            }

            .search-field:last-of-type {
                border-bottom: none;
            }

            .search-divider {
                display: none;
            }

            .search-submit-btn {
                width: 100%;
                justify-content: center;
                margin-top: 0.5rem;
                padding: 1rem;
            }

            .location-btn {
                right: 0;
            }
        }

        @media (max-width: 768px) {
            .jobs-header {
                padding: 2rem 0 1.5rem;
                border-radius: 0 0 24px 24px;
            }

            .jobs-title {
                font-size: 1.75rem;
            }

            .jobs-subtitle {
                font-size: 0.95rem;
            }

            .header-badge {
                font-size: 0.75rem;
                padding: 0.4rem 0.875rem;
            }

            .quick-search-tags {
                gap: 0.375rem;
            }

            .quick-tag {
                padding: 0.35rem 0.75rem;
                font-size: 0.75rem;
            }

            .header-stats-row {
                gap: 0.5rem;
            }

            .stat-pill {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
            }

            .decoration-dots,
            .decoration-grid {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .jobs-header {
                padding: 1.5rem 0 1.25rem;
                margin: -1rem -1rem 1rem -1rem;
                width: calc(100% + 2rem);
                border-radius: 0 0 20px 20px;
            }

            .jobs-title {
                font-size: 1.5rem;
            }

            .jobs-subtitle {
                font-size: 0.875rem;
            }

            .search-field {
                padding: 0.75rem;
            }

            .search-field .search-input {
                font-size: 0.875rem;
            }

            .quick-label {
                display: none;
            }

            .header-stats-row {
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
            }
        }

        /* === Full Width Content Wrapper === */
        .jobs-content-wrapper {
            max-width: 1500px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .jobs-main-full {
            min-width: 0;
            padding: 0;
            background: transparent;
        }

        /* Sort Form Inline */
        .sort-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* === Smart Match Banner === */
        .smart-match-banner {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .smart-match-banner.active {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%);
            border: 1px solid #6ee7b7;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.12);
        }

        .smart-match-banner.active:hover {
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.18);
            transform: translateY(-2px);
        }

        .smart-match-banner.inactive {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fde68a 100%);
            border: 1px solid #fbbf24;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.12);
        }

        .smart-match-banner.inactive:hover {
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.18);
            transform: translateY(-2px);
        }

        .smart-match-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .smart-match-banner.active .smart-match-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }

        .smart-match-banner.inactive .smart-match-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
        }

        .smart-match-content {
            flex: 1;
            min-width: 0;
        }

        .smart-match-content strong {
            display: block;
            font-size: 0.95rem;
            color: #1e293b;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .smart-match-content span {
            font-size: 0.85rem;
            color: #64748b;
        }

        .smart-match-settings {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.06);
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.25s ease;
            font-size: 0.9rem;
        }

        .smart-match-settings:hover {
            background: rgba(0, 0, 0, 0.12);
            color: #1e293b;
            transform: rotate(90deg);
        }

        .smart-match-activate {
            padding: 0.625rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .smart-match-activate:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        /* === Recommended Jobs === */
        .recommended-jobs-section {
            background: linear-gradient(135deg, #312e81 0%, #4338ca 40%, #6366f1 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(79, 70, 229, 0.25);
            position: relative;
            overflow: hidden;
        }

        .recommended-jobs-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .recommended-jobs-section .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .section-title-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title-group i {
            color: #fbbf24;
            font-size: 1.125rem;
            filter: drop-shadow(0 2px 4px rgba(251, 191, 36, 0.4));
        }

        .section-title-group h2 {
            color: #fff;
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .powered-badge {
            font-size: 0.7rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
        }

        .recommended-jobs-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
            position: relative;
            z-index: 1;
        }

        .recommended-job-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .recommended-job-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
        }

        .match-score {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.35rem 0.625rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        }

        .recommended-job-content {
            padding: 1.25rem 1rem 1rem;
            text-align: center;
        }

        .company-initial {
            width: 48px;
            height: 48px;
            margin: 0 auto 0.75rem;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .recommended-job-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.375rem;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .recommended-company {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0 0 0.625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-weight: 500;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .recommended-tags {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .recommended-tags .tag-type,
        .recommended-tags .tag-category {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.625rem;
            border-radius: 6px;
        }

        .tag-type {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #4f46e5;
        }

        .tag-category {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #16a34a;
        }

        .recommended-job-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.75rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #4f46e5;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-top: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .recommended-job-link:hover {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #4338ca;
        }

        .recommended-job-link i {
            font-size: 0.7rem;
            transition: transform 0.25s ease;
        }

        .recommended-job-link:hover i {
            transform: translateX(4px);
        }

        /* === Results Header - Elegant Style === */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.75rem;
            background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .results-info {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .results-count {
            font-size: 0.95rem;
            color: #64748b;
        }

        .results-count strong {
            color: #4f46e5;
            font-weight: 700;
            font-size: 1.125rem;
        }

        .active-filters {
            display: flex;
            gap: 0.625rem;
            flex-wrap: wrap;
        }

        .active-filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #4f46e5;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            padding: 0.5rem 0.875rem;
            border-radius: 25px;
            border: 1px solid #c7d2fe;
            transition: all 0.2s ease;
        }

        .active-filter-tag:hover {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            transform: translateY(-1px);
        }

        .active-filter-tag i {
            font-size: 0.7rem;
            opacity: 0.85;
        }

        .results-sort {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .results-sort label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
        }

        .results-sort select {
            padding: 0.625rem 1rem;
            font-size: 0.85rem;
            color: #334155;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.25s ease;
            min-width: 120px;
        }

        .results-sort select:hover {
            border-color: #c7d2fe;
        }

        .results-sort select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        /* === Job Cards - Professional Grid Layout === */
        .job-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.75rem;
        }

        .job-card-grid {
            background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 1.75rem;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .job-card-grid::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            border-radius: 20px 20px 0 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .job-card-grid:hover {
            border-color: #c7d2fe;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.12), 0 8px 16px rgba(79, 70, 229, 0.08);
            transform: translateY(-6px);
        }

        .job-card-grid:hover::before {
            opacity: 1;
        }

        /* Card Header */
        .job-card-header {
            margin-bottom: 1.25rem;
        }

        .job-card-top {
            display: flex;
            align-items: flex-start;
            gap: 1.125rem;
            margin-bottom: 0.875rem;
        }

        .job-company-logo {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.375rem;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
            transition: all 0.3s ease;
        }

        .job-card-grid:hover .job-company-logo {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
        }

        .job-header-info {
            flex: 1;
            min-width: 0;
        }

        .job-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            line-height: 1.35;
        }

        .job-title a {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.25s ease;
        }

        .job-title a:hover {
            color: #4f46e5;
        }

        .job-company {
            font-size: 0.825rem;
            color: #64748b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .job-menu-btn {
            width: 34px;
            height: 34px;
            border: 1px solid transparent;
            background: transparent;
            color: #94a3b8;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            flex-shrink: 0;
        }

        .job-menu-btn:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #64748b;
        }

        .job-location {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
        }

        .job-location i {
            color: #10b981;
            font-size: 0.75rem;
        }

        /* Card Body */
        .job-card-grid .job-card-body {
            flex: 1;
            padding: 0;
            display: block;
        }

        .job-info-row {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
        }

        .job-info-item {
            display: flex;
            flex-direction: column;
            gap: 0;
            padding: 0.5rem 0.875rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .job-info-item .info-label {
            font-size: 0.825rem;
            font-weight: 600;
            color: #334155;
        }

        .job-info-item.salary {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #a7f3d0;
        }

        .job-info-item.salary .info-label {
            color: #047857;
            font-weight: 700;
        }

        .job-description {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.65;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* Card Tags */
        .job-card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }

        .job-tag {
            display: inline-block;
            padding: 0.4rem 0.875rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.25s ease;
        }

        .job-tag:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        /* Card Footer */
        .job-card-grid .job-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
            background: transparent;
        }

        .job-posted {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .job-posted i {
            font-size: 0.75rem;
            color: #a5b4fc;
        }

        .job-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .job-action-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: #94a3b8;
        }

        .job-action-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .job-action-btn.save:hover {
            border-color: #fecaca;
            background: #fef2f2;
            color: #ef4444;
        }

        /* Fix for save job button - disable tooltips and overlays */
        .job-card-grid .save-job-btn,
        .job-card-grid .job-action-btn,
        .job-actions .btn {
            position: relative;
        }

        .job-card-grid .save-job-btn::before,
        .job-card-grid .save-job-btn::after,
        .job-card-grid .job-action-btn::before,
        .job-card-grid .job-action-btn::after,
        .job-actions .btn::before,
        .job-actions .btn::after {
            display: none !important;
            content: none !important;
        }

        /* Completely disable all tooltips and popovers on job cards */
        .job-card-grid [title],
        .job-card-grid [data-bs-toggle],
        .job-actions [title],
        .job-actions [data-bs-toggle],
        .job-menu-btn[title],
        .job-grid [title] {
            pointer-events: auto;
        }

        .job-card-grid .tooltip,
        .job-actions .tooltip,
        .job-grid .tooltip,
        .tooltip.show {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }

        /* Hide Bootstrap popover on job cards */
        .job-card-grid .popover,
        .job-actions .popover,
        .job-grid .popover,
        .popover.show {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }

        /* Prevent any hover popup/overlay on job elements */
        .job-card-grid *,
        .job-grid * {
            --bs-tooltip-opacity: 0;
        }

        /* Disable menu button tooltip behavior */
        .job-menu-btn {
            pointer-events: auto !important;
        }

        /* Style the save button properly */
        .job-actions .save-job-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: #94a3b8;
        }

        .job-actions .save-job-btn:hover {
            border-color: #fecaca;
            background: #fef2f2;
            color: #ef4444;
        }

        .job-actions .save-job-btn.btn-success {
            background: #dcfce7;
            border-color: #86efac;
            color: #16a34a;
        }

        .job-actions .save-job-btn.btn-success:hover {
            background: #bbf7d0;
            border-color: #4ade80;
        }

        .job-actions .save-job-btn i {
            margin: 0 !important;
            font-size: 0.85rem;
        }

        /* View Job Button */
        .job-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #4f46e5;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .job-view-btn:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            border-color: #4f46e5;
            transform: translateX(2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .job-view-btn i {
            font-size: 0.7rem;
            transition: transform 0.25s ease;
        }

        .job-view-btn:hover i {
            transform: translateX(3px);
        }

        /* Tag Variations */
        .job-tag.primary {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #4f46e5;
            border-color: #c7d2fe;
            font-weight: 700;
        }

        /* Responsive Grid - 3 Column Layout */
        @media (max-width: 1200px) {
            .job-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .job-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .job-card-grid {
                padding: 1rem;
            }

            .job-info-row {
                flex-wrap: wrap;
                gap: 0.75rem 1.25rem;
            }
        }

        /* === Pagination === */
        .pagination-container {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        .pagination-container .pagination {
            background: #fff;
            border-radius: 12px;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 0.25rem;
        }

        .pagination-container .page-item .page-link {
            padding: 0.625rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border: none;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .pagination-container .page-item.active .page-link {
            background: #4f46e5;
            color: #fff;
        }

        .pagination-container .page-item .page-link:hover {
            background: #f1f5f9;
            color: #4f46e5;
        }

        .pagination-container .page-item.disabled .page-link {
            color: #cbd5e1;
        }

        /* === Empty State === */
        .empty-state-card {
            text-align: center;
            padding: 3rem 2rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .empty-state-card .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.25rem;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state-card .empty-icon i {
            font-size: 1.5rem;
            color: #94a3b8;
        }

        .empty-state-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem;
        }

        .empty-state-card p {
            font-size: 0.9rem;
            color: #64748b;
            margin: 0 0 1.5rem;
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-reset-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #fff;
            background: #4f46e5;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .empty-reset-btn:hover {
            background: #4338ca;
            color: #fff;
            transform: translateY(-1px);
        }

        /* === Responsive Design === */
        @media (max-width: 1200px) {
            .recommended-jobs-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .recommended-jobs-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .jobs-header {
                padding: 0.75rem 0;
                margin: -1rem -1rem 0.75rem -1rem;
                width: calc(100% + 2rem);
            }

            .jobs-header-inner {
                flex-direction: column;
                text-align: center;
                gap: 0.625rem;
            }

            .results-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .results-info {
                flex-direction: column;
                gap: 0.375rem;
            }

            .job-card-body {
                flex-direction: column;
                padding: 0.75rem;
            }

            .job-company-logo {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .job-title-row {
                flex-direction: column;
                gap: 0.375rem;
            }

            .job-card-footer {
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.5rem 0.75rem;
            }

            .job-apply-btn {
                width: 100%;
                justify-content: center;
            }

            .recommended-jobs-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .jobs-title {
                font-size: 1.1rem;
            }

            .jobs-stat-badge .stat-value {
                font-size: 1.25rem;
            }

            .job-meta-row {
                gap: 0.25rem 0.5rem;
            }
        }

        /* === Animations === */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .job-card-grid {
            animation: fadeInUp 0.4s ease-out backwards;
        }

        .job-card-grid:nth-child(1) {
            animation-delay: 0.05s;
        }

        .job-card-grid:nth-child(2) {
            animation-delay: 0.1s;
        }

        .job-card-grid:nth-child(3) {
            animation-delay: 0.15s;
        }

        .job-card-grid:nth-child(4) {
            animation-delay: 0.2s;
        }

        .job-card-grid:nth-child(5) {
            animation-delay: 0.25s;
        }

        .job-card-grid:nth-child(6) {
            animation-delay: 0.3s;
        }

        .recommended-job-card {
            animation: fadeInUp 0.4s ease-out;
        }

        /* Large screens */
        @media (min-width: 1600px) {
            .jobs-page-container {
                padding: 0 2.5rem 4rem;
            }

            .jobs-header {
                padding: 4rem 0 3.5rem;
            }

            .jobs-header-inner {
                max-width: 1500px;
                padding: 0 3.5rem;
            }

            .header-search-container {
                max-width: 1000px;
            }

            .jobs-title {
                font-size: 3.25rem;
            }

            .jobs-subtitle {
                font-size: 1.25rem;
                max-width: 640px;
            }

            .jobs-content-wrapper {
                max-width: 1700px;
                padding: 0 2rem;
            }

            .job-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 2rem;
            }

            .job-card-grid {
                padding: 2rem;
            }

            .recommended-jobs-section {
                padding: 2.5rem;
            }

            .recommended-jobs-grid {
                gap: 1.5rem;
            }
        }

        @media (min-width: 1920px) {
            .jobs-page-container {
                padding: 0 3rem 5rem;
            }

            .jobs-content-wrapper {
                max-width: 1900px;
                padding: 0 3rem;
            }

            .job-grid {
                gap: 2.25rem;
            }

            .job-card-grid {
                padding: 2.25rem;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Disable all Bootstrap tooltips and popovers on job cards
            // Remove title attributes that might trigger tooltips
            document.querySelectorAll('.job-grid [title], .job-card-grid [title]').forEach(function (el) {
                el.removeAttribute('title');
            });

            // Dispose any existing Bootstrap tooltips/popovers on job elements
            document.querySelectorAll('.job-grid *, .job-card-grid *').forEach(function (el) {
                // Dispose tooltip if exists
                if (bootstrap && bootstrap.Tooltip) {
                    var tooltip = bootstrap.Tooltip.getInstance(el);
                    if (tooltip) tooltip.dispose();
                }
                // Dispose popover if exists
                if (bootstrap && bootstrap.Popover) {
                    var popover = bootstrap.Popover.getInstance(el);
                    if (popover) popover.dispose();
                }
            });

            // Header search form submission with loading state
            const headerSearchForm = document.getElementById('headerSearchForm');
            if (headerSearchForm) {
                headerSearchForm.addEventListener('submit', function () {
                    const searchBtn = this.querySelector('.search-submit-btn');
                    if (searchBtn) {
                        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Searching...</span>';
                        searchBtn.disabled = true;
                    }
                });
            }

            // Clear header location coordinates when typing
            const headerLocation = document.getElementById('headerLocation');
            if (headerLocation) {
                headerLocation.addEventListener('input', function () {
                    document.getElementById('header_location_latitude').value = '';
                    document.getElementById('header_location_longitude').value = '';
                });
            }
        });

        // Header Search Geolocation function
        function useHeaderLocation() {
            const btn = document.querySelector('.location-btn');
            const originalHtml = btn.innerHTML;
            const mapboxToken = '{{ config("mapbox.public_token") }}';

            if (!window.isSecureContext) {
                showNotification('Location requires HTTPS connection.', 'error');
                return;
            }

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        document.getElementById('header_location_latitude').value = lat;
                        document.getElementById('header_location_longitude').value = lng;

                        // Also update sidebar fields if they exist
                        if (document.getElementById('location_filter_latitude')) {
                            document.getElementById('location_filter_latitude').value = lat;
                            document.getElementById('location_filter_longitude').value = lng;
                        }

                        if (mapboxToken && mapboxToken.length > 10) {
                            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${mapboxToken}&types=locality,place,neighborhood&limit=1`)
                                .then(r => r.json())
                                .then(data => {
                                    let locationName = 'Near Me';
                                    if (data.features && data.features.length > 0) {
                                        locationName = data.features[0].place_name.split(',')[0].trim();
                                    }
                                    document.getElementById('headerLocation').value = locationName;
                                    if (document.getElementById('location')) {
                                        document.getElementById('location').value = locationName;
                                    }
                                    btn.innerHTML = '<i class="fas fa-check"></i>';
                                    btn.style.background = '#10b981';
                                    btn.style.color = '#fff';
                                    showNotification(`Location: ${locationName}`, 'success');
                                    resetHeaderBtn(2500);
                                })
                                .catch(() => {
                                    document.getElementById('headerLocation').value = 'Near Me';
                                    btn.innerHTML = '<i class="fas fa-check"></i>';
                                    showNotification('Location detected!', 'success');
                                    resetHeaderBtn(2000);
                                });
                        } else {
                            document.getElementById('headerLocation').value = 'Near Me';
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            showNotification('Location detected!', 'success');
                            resetHeaderBtn(2000);
                        }
                    },
                    function (error) {
                        btn.innerHTML = '<i class="fas fa-times"></i>';
                        btn.style.background = '#fee2e2';
                        btn.style.color = '#ef4444';
                        let msg = 'Unable to detect location.';
                        if (error.code === error.PERMISSION_DENIED) msg = 'Location access denied.';
                        showNotification(msg, 'error');
                        resetHeaderBtn(2500);
                    },
                    { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 }
                );
            } else {
                showNotification('Geolocation not supported.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }

            function resetHeaderBtn(delay) {
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.style.background = '';
                    btn.style.color = '';
                    btn.disabled = false;
                }, delay);
            }
        }

        // Toast notification
        function showNotification(message, type = 'info') {
            const existing = document.querySelectorAll('.toast-notification');
            existing.forEach(n => n.remove());

            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

            const colors = { success: '#10b981', error: '#ef4444', info: '#4f46e5' };
            toast.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: ${colors[type] || colors.info};
            color: #fff;
            padding: 0.875rem 1.25rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            z-index: 9999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        `;

            document.body.appendChild(toast);
            setTimeout(() => toast.style.transform = 'translateX(0)', 10);
            setTimeout(() => {
                toast.style.transform = 'translateX(120%)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>
@endpush