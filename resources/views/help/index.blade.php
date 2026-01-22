@extends('front.layouts.app')

@section('content')
    <div class="faq-section">
        <!-- Hero Section -->
        <div class="faq-hero">
            <div class="faq-hero-bg"></div>
            <div class="container text-center position-relative z-1">
                <!-- Added text-shadow and increased opacity for better readability -->
                <h1 class="display-4 fw-bold mb-3 text-white hero-title">How can we help you?</h1>
                <p class="lead text-white mb-5 hero-subtitle" style="opacity: 0.9; font-weight: 500;">Choose your role to
                    find the right guide for your journey.</p>
            </div>
            <div class="hero-shape">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                    <path fill="#f9fafb" fill-opacity="1"
                        d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
                    </path>
                </svg>
            </div>
        </div>

        <!-- Cards Section -->
        <div class="container section-cards">
            <div class="row justify-content-center g-4">
                <!-- Job Seeker Card -->
                <div class="col-md-6 col-lg-5">
                    <div class="role-card h-100">
                        <div class="card-line-top jobseeker-line"></div>
                        <div class="card-icon-wrapper jobseeker-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="card-body text-center mt-4">
                            <h3 class="card-title h2 fw-bold mb-3 text-dark">I am a Job Seeker</h3>
                            <!-- Changed text-muted to a specific dark gray for clarity -->
                            <p class="card-text mb-4" style="color: #4b5563; font-size: 1.05rem;">
                                Looking for your dream job? We'll guide you through creating a standout profile, searching
                                for opportunities, and submitting winning applications.
                            </p>
                            <a href="{{ route('help.jobseeker') }}"
                                class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm hover-lift">
                                View Job Seeker Guide <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Employer Card -->
                <div class="col-md-6 col-lg-5">
                    <div class="role-card h-100">
                        <div class="card-line-top employer-line"></div>
                        <div class="card-icon-wrapper employer-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="card-body text-center mt-4">
                            <h3 class="card-title h2 fw-bold mb-3 text-dark">I am an Employer</h3>
                            <!-- Changed text-muted to a specific dark gray for clarity -->
                            <p class="card-text mb-4" style="color: #4b5563; font-size: 1.05rem;">
                                Need to hire top talent? Learn how to set up your company profile, post engaging job
                                listings, and manage your candidates effectively.
                            </p>
                            <a href="{{ route('help.employer') }}"
                                class="btn btn-outline-primary btn-lg rounded-pill px-5 shadow-sm hover-lift">
                                View Employer Guide <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Styles for Help Center */
        .faq-section {
            background-color: #f9fafb;
            /* Light gray background for the whole section to match wave */
        }

        .faq-hero {
            position: relative;
            padding: 120px 0 180px;
            margin-bottom: -100px;
            overflow: hidden;
        }

        .faq-hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, #8b5cf6, #4f46e5);
            z-index: 0;
        }

        .hero-title {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .section-cards {
            padding-bottom: 80px;
            position: relative;
            z-index: 2;
        }

        .hero-shape {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            z-index: 1;
        }

        .role-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            /* Darker shadow definition */
            padding: 50px 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-line-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
        }

        .jobseeker-line {
            background: #4f46e5;
        }

        .employer-line {
            background: #a855f7;
        }

        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(79, 70, 229, 0.2);
            /* Colored shadow on hover */
        }

        .card-icon-wrapper {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 3rem;
            background: #f3f4f6;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .role-card:hover .card-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .role-card:hover .jobseeker-icon {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .role-card:hover .employer-icon {
            background: #f3e8ff;
            color: #9333ea;
        }

        .jobseeker-icon {
            color: #6366f1;
            background: #eef2ff;
        }

        .employer-icon {
            color: #a855f7;
            background: #fafafe;
        }

        .btn-primary {
            background: #4f46e5;
            border: none;
            font-weight: 600;
            padding: 12px 30px;
        }

        .btn-primary:hover {
            background: #4338ca;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .btn-outline-primary {
            color: #4f46e5;
            border: 2px solid #4f46e5;
            font-weight: 600;
            padding: 12px 30px;
        }

        .btn-outline-primary:hover {
            background: #4f46e5;
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection