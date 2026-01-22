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
                        <h1 class="guide-title fw-bold mb-2">Job Seeker's Journey</h1>
                        <p class="lead text-white-50">Your step-by-step roadmap to finding the perfect job.</p>
                    </div>
                    <div class="col-md-4 text-end d-none d-md-block">
                        <i class="fas fa-map-marked-alt fa-8x text-white-50" style="opacity: 0.2;"></i>
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
                        <h2 class="timeline-title">Create Your Account</h2>
                        <p class="text-muted mb-3">Start your journey by registering on the platform.</p>
                        <ul class="guide-list">
                            <li>Click <strong>"Get Started"</strong> in the navigation bar.</li>
                            <li>Select <strong>"Register"</strong> and choose <strong>"Candidate"</strong>.</li>
                            <li>Verify your email to activate your account.</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="timeline-item">
                    <div class="timeline-marker">2</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title">Build Your Profile</h2>
                        <p class="text-muted mb-3">Make a great first impression with a complete profile.</p>
                        <ul class="guide-list">
                            <li>Upload a professional photo.</li>
                            <li>Add your <strong>Resume/CV</strong>.</li>
                            <li>List your skills, education, and experience.</li>
                            <div class="alert alert-info mt-3 border-0 bg-soft-primary">
                                <i class="fas fa-lightbulb me-2"></i> <strong>Tip:</strong> Users with 80%+ profile
                                completion get 3x more views!
                            </div>
                        </ul>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="timeline-item">
                    <div class="timeline-marker">3</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title">Find Jobs</h2>
                        <p class="text-muted mb-3">Search efficiently using our smart filters.</p>
                        <ul class="guide-list">
                            <li>Go to the <strong>"Find Jobs"</strong> page.</li>
                            <li>Filter by <strong>Keyword, Location, or Category</strong>.</li>
                            <li>Save interesting jobs to apply later.</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="timeline-item">
                    <div class="timeline-marker">4</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title">Apply & Track</h2>
                        <p class="text-muted mb-3">Submit applications and monitor their status.</p>
                        <ul class="guide-list">
                            <li>Click <strong>"Apply Now"</strong> on a job listing.</li>
                            <li>Answer any screening questions.</li>
                            <li>Track status in <strong>"My Career"</strong> (Applied, Shortlisted, Hired).</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .guide-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
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
            /* Alignment line */
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
            /* (Left padding + line width) center alignment */
            top: 0;
            width: 40px;
            height: 40px;
            background: #4f46e5;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            font-size: 1.2rem;
            border: 4px solid #fff;
            box-shadow: 0 0 0 3px #e0e7ff;
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
            color: #10b981;
        }

        .bg-soft-primary {
            background-color: #eff6ff;
            color: #1e40af;
        }
    </style>
@endsection