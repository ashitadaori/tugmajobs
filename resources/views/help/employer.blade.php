@extends('front.layouts.app')

@section('content')
    <div class="guide-wrapper">
        <!-- Hero Header -->
        <div class="guide-hero text-white">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <a href="{{ route('help.index') }}"
                            class="back-link mb-3 d-inline-block text-white-50 text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i> Help Center
                        </a>
                        <h1 class="guide-title fw-bold mb-2">Employer's Guide</h1>
                        <p class="lead text-white-50">Hire better, faster, and smarter with TugmaJobs.</p>
                    </div>
                    <div class="col-md-4 text-end d-none d-md-block">
                        <i class="fas fa-briefcase fa-8x text-white-50" style="opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <div class="timeline">

                <!-- Step 1 -->
                <div class="timeline-item">
                    <div class="timeline-marker">1</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title">Register Your Company</h2>
                        <p class="text-muted mb-3">Establish your presence on the platform.</p>
                        <ul class="guide-list">
                            <li>Select <strong>"Employer"</strong> when registering.</li>
                            <li>Fill in your business details.</li>
                            <li>Verify your email to access the dashboard.</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="timeline-item">
                    <div class="timeline-marker">2</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title">Brand Your Profile</h2>
                        <p class="text-muted mb-3">Attract talent with a compelling company page.</p>
                        <ul class="guide-list">
                            <li>Go to <strong>"Company Profile"</strong> in the dashboard.</li>
                            <li>Upload your <strong>logo and banner</strong>.</li>
                            <li>Write a description about your culture and values.</li>
                            <div class="alert alert-warning mt-3 border-0 bg-soft-warning">
                                <i class="fas fa-shield-alt me-2"></i> <strong>Note:</strong> Complete KYC verification to
                                gain the "Verified" badge.
                            </div>
                        </ul>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="timeline-item">
                    <div class="timeline-marker">3</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title">Post Jobs</h2>
                        <p class="text-muted mb-3">Reach thousands of qualified candidates.</p>
                        <ul class="guide-list">
                            <li>Click <strong>"Post a Job"</strong>.</li>
                            <li>Add specific requirements, salary range, and job type.</li>
                            <li>Use relevant tags to help candidates find your listing.</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="timeline-item">
                    <div class="timeline-marker">4</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title">Hire the Best</h2>
                        <p class="text-muted mb-3">Manage and select your top candidates.</p>
                        <ul class="guide-list">
                            <li>Review applications in the <strong>"Manage Jobs"</strong> section.</li>
                            <li>Shortlist promising candidates.</li>
                            <li>Schedule interviews and send offers directly.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .guide-hero {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            padding: 60px 0;
            margin-bottom: 40px;
            border-radius: 0 0 30px 30px;
        }

        .timeline {
            position: relative;
            padding-left: 20px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 35px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #e5e7eb;
            border-radius: 2px;
        }

        .timeline-item {
            position: relative;
            padding-left: 60px;
            margin-bottom: 50px;
        }

        .timeline-marker {
            position: absolute;
            left: 17px;
            top: 0;
            width: 40px;
            height: 40px;
            background: #7c3aed;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            font-size: 1.2rem;
            border: 4px solid #fff;
            box-shadow: 0 0 0 3px #f3e8ff;
            z-index: 10;
        }

        .timeline-content {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            transition: transform 0.3s ease;
        }

        .timeline-content:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .timeline-title {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .guide-list {
            list-style: none;
            padding: 0;
        }

        .guide-list li {
            margin-bottom: 12px;
            position: relative;
            padding-left: 30px;
            color: #4b5563;
        }

        .guide-list li::before {
            content: '\f058';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: #8b5cf6;
        }

        .bg-soft-warning {
            background-color: #fffbeb;
            color: #b45309;
        }
    </style>
@endsection