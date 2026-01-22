<!-- Jobseeker Help Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="jobseekerHelpOffcanvas"
    aria-labelledby="jobseekerHelpOffcanvasLabel" style="width: 100%; max-width: 600px;">
    <div class="offcanvas-header text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
        <h5 class="offcanvas-title" id="jobseekerHelpOffcanvasLabel">
            <i class="fas fa-question-circle me-2"></i> Job Seeker Guide
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- Hero Header (Mini) -->
        <div class="bg-light p-4 text-center border-bottom">
            <h2 class="h4 fw-bold mb-2">Your Journey Starts Here</h2>
            <p class="text-muted small mb-0">Follow these steps to land your dream job.</p>
        </div>

        <div class="p-4">
            <div class="timeline">

                <!-- Step 1 -->
                <div class="timeline-item">
                    <div class="timeline-marker">1</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title h6">Create Your Account</h2>
                        <ul class="guide-list small text-muted">
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
                        <h2 class="timeline-title h6">Build Your Profile</h2>
                        <ul class="guide-list small text-muted">
                            <li>Upload a professional photo.</li>
                            <li>Add your <strong>Resume/CV</strong>.</li>
                            <li>List your skills, education, and experience.</li>
                            <div class="alert alert-info mt-2 mb-0 py-2 px-3 small border-0 bg-soft-primary">
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
                            <li>Track status in <strong>"My Career"</strong> (Applied, Shortlisted, Hired).</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Scoped Styles for Offcanvas Timeline */
    #jobseekerHelpOffcanvas .timeline {
        position: relative;
        padding-left: 10px;
    }

    #jobseekerHelpOffcanvas .timeline::before {
        content: '';
        position: absolute;
        left: 24px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    #jobseekerHelpOffcanvas .timeline-item {
        position: relative;
        padding-left: 45px;
        margin-bottom: 30px;
    }

    #jobseekerHelpOffcanvas .timeline-marker {
        position: absolute;
        left: 10px;
        top: 0;
        width: 30px;
        height: 30px;
        background: #4f46e5;
        color: white;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: bold;
        font-size: 0.9rem;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e7ff;
        z-index: 10;
    }

    #jobseekerHelpOffcanvas .timeline-content {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #f3f4f6;
    }

    #jobseekerHelpOffcanvas .guide-list {
        list-style: none;
        padding: 0;
        margin-bottom: 0;
    }

    #jobseekerHelpOffcanvas .guide-list li {
        margin-bottom: 8px;
        position: relative;
        padding-left: 20px;
    }

    #jobseekerHelpOffcanvas .guide-list li::before {
        content: '\f058';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 2px;
        color: #10b981;
        font-size: 0.8rem;
    }

    #jobseekerHelpOffcanvas .bg-soft-primary {
        background-color: #eff6ff;
        color: #1e40af;
    }
</style>