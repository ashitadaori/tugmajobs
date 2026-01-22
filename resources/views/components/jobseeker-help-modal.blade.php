<!-- Jobseeker Help Modal Component -->
<div class="modal fade" id="jobseekerHelpModal" tabindex="-1" aria-labelledby="jobseekerHelpModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Side - Branding Section -->
                    <div
                        class="col-md-5 help-welcome-section d-flex align-items-center justify-content-center position-relative">
                        <div class="help-pattern"></div>
                        <div class="text-center text-white p-5 position-relative z-index-2">
                            <div class="help-logo mb-4">
                                <i class="fas fa-question-circle fa-3x"></i>
                            </div>
                            <h3 class="fw-bold mb-3 help-title">Job Seeker<br>Guide</h3>
                            <p class="mb-4 text-white help-subtitle">Your step-by-step roadmap to finding the perfect
                                job.</p>

                            <div class="help-features mt-4 text-start">
                                <div class="feature-item mb-3">
                                    <i class="fas fa-search me-2"></i>
                                    <span>Smart job searching</span>
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-file-alt me-2"></i>
                                    <span>Profile optimization</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    <span>Easy applications</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Content Section -->
                    <div class="col-md-7 p-0 position-relative bg-white">
                        <!-- Close Button -->
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-index-10"
                            data-bs-dismiss="modal" aria-label="Close"></button>

                        <div class="help-modal-content p-4 p-md-5 h-100 overflow-auto" style="max-height: 80vh;">
                            <h2 class="fw-bold mb-4 text-dark h4">How it works</h2>

                            <div class="timeline">
                                <!-- Step 1 -->
                                <div class="timeline-item">
                                    <div class="timeline-marker">1</div>
                                    <div class="timeline-content">
                                        <h2 class="timeline-title h6">Create Your Account</h2>
                                        <ul class="guide-list small text-muted">
                                            <li>Click <strong>"Get Started"</strong> in the navigation bar.</li>
                                            <li>Select <strong>"Register"</strong> and choose
                                                <strong>"Candidate"</strong>.</li>
                                            <li>Verify your email to activate your account.</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Step 2 -->
                                <div class="timeline-item">
                                    <div class="timeline-marker">2</div>
                                    <div class="timeline-content">
                                        <h2 class="timeline-title h6">Build Your Profile</h2>
                                        <ul class="guide-list small text-muted">
                                            <li>Upload a professional photo.</li>
                                            <li>Add your <strong>Resume/CV</strong>.</li>
                                            <li>List your skills, education, and experience.</li>
                                            <div
                                                class="alert alert-info mt-2 mb-0 py-2 px-3 small border-0 bg-soft-primary">
                                                <i class="fas fa-lightbulb me-2"></i> <strong>Tip:</strong> Users with
                                                80%+ profile completion get 3x more views!
                                            </div>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="timeline-item">
                                    <div class="timeline-marker">3</div>
                                    <div class="timeline-content">
                                        <h2 class="timeline-title h6">Find Jobs</h2>
                                        <ul class="guide-list small text-muted">
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
                                        <h2 class="timeline-title h6">Apply & Track</h2>
                                        <ul class="guide-list small text-muted">
                                            <li>Click <strong>"Apply Now"</strong> on a job listing.</li>
                                            <li>Answer any screening questions.</li>
                                            <li>Track status in <strong>"My Career"</strong>.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top text-center">
                                <p class="small text-muted mb-2">Still have questions?</p>
                                <a href="mailto:support@tugmajobs.com"
                                    class="btn btn-sm btn-outline-primary rounded-pill px-4">Contact Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .help-welcome-section {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        min-height: 100%;
        position: relative;
        overflow: hidden;
    }

    .help-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 2px),
            radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 2px, transparent 2px);
        background-size: 50px 50px;
        background-position: 0 0, 25px 25px;
    }

    .help-logo {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        backdrop-filter: blur(10px);
    }

    .help-title {
        font-size: 1.75rem;
        line-height: 1.2;
    }

    .help-features {
        font-size: 0.9rem;
    }

    .z-index-10 {
        z-index: 10;
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 10px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 15px;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item {
        position: relative;
        padding-left: 45px;
        margin-bottom: 25px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 32px;
        height: 32px;
        background: #4f46e5;
        color: white;
        border-radius: 50%;
        text-align: center;
        line-height: 32px;
        font-weight: bold;
        font-size: 0.9rem;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e7ff;
        z-index: 10;
    }

    .timeline-content {
        background: #f8fafc;
        padding: 15px 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .guide-list {
        list-style: none;
        padding: 0;
        margin-bottom: 0;
    }

    .guide-list li {
        margin-bottom: 6px;
        position: relative;
        padding-left: 18px;
        line-height: 1.5;
    }

    .guide-list li:last-child {
        margin-bottom: 0;
    }

    .guide-list li::before {
        content: '\f058';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 3px;
        color: #10b981;
        font-size: 0.75rem;
    }

    .bg-soft-primary {
        background-color: #eff6ff !important;
        color: #1e40af !important;
    }

    @media (max-width: 768px) {
        .help-welcome-section {
            min-height: 200px;
            padding: 2rem !important;
        }

        .help-logo {
            width: 60px;
            height: 60px;
        }

        .help-logo i {
            font-size: 2rem;
        }

        .help-title {
            font-size: 1.5rem;
        }

        .help-features {
            display: none;
        }
    }
</style>