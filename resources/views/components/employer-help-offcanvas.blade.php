<!-- Employer Help Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="employerHelpOffcanvas"
    aria-labelledby="employerHelpOffcanvasLabel" style="width: 100%; max-width: 600px;">
    <div class="offcanvas-header text-white" style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);">
        <h5 class="offcanvas-title" id="employerHelpOffcanvasLabel">
            <i class="fas fa-briefcase me-2"></i> Employer Guide
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- Hero Header (Mini) -->
        <div class="bg-light p-4 text-center border-bottom">
            <h2 class="h4 fw-bold mb-2">Hire Smarter</h2>
            <p class="text-muted small mb-0">Optimize your recruitment process with these steps.</p>
        </div>

        <div class="p-4">
            <div class="timeline">

                <!-- Step 1 -->
                <div class="timeline-item">
                    <div class="timeline-marker">1</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title h6">Register Your Company</h2>
                        <ul class="guide-list small text-muted">
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
                        <h2 class="timeline-title h6">Brand Your Profile</h2>
                        <ul class="guide-list small text-muted">
                            <li>Go to <strong>"Company Profile"</strong> in the dashboard.</li>
                            <li>Upload your <strong>logo and banner</strong>.</li>
                            <li>Write a description about your including culture and values.</li>
                            <div class="alert alert-warning mt-2 mb-0 py-2 px-3 small border-0 bg-soft-warning">
                                <i class="fas fa-shield-alt me-2"></i> <strong>Note:</strong> Complete KYC verification
                                to gain the "Verified" badge.
                            </div>
                        </ul>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="timeline-item">
                    <div class="timeline-marker">3</div>
                    <div class="timeline-content">
                        <h2 class="timeline-title h6">Post Jobs</h2>
                        <ul class="guide-list small text-muted">
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
                        <h2 class="timeline-title h6">Hire the Best</h2>
                        <ul class="guide-list small text-muted">
                            <li>Review applications in the <strong>"Manage Jobs"</strong> section.</li>
                            <li>Shortlist promising candidates.</li>
                            <li>Schedule interviews and send offers directly.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Scoped Styles for Offcanvas Timeline */
    #employerHelpOffcanvas .timeline {
        position: relative;
        padding-left: 10px;
    }

    #employerHelpOffcanvas .timeline::before {
        content: '';
        position: absolute;
        left: 24px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    #employerHelpOffcanvas .timeline-item {
        position: relative;
        padding-left: 45px;
        margin-bottom: 30px;
    }

    #employerHelpOffcanvas .timeline-marker {
        position: absolute;
        left: 10px;
        top: 0;
        width: 30px;
        height: 30px;
        background: #7c3aed;
        color: white;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: bold;
        font-size: 0.9rem;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #ddd6fe;
        z-index: 10;
    }

    #employerHelpOffcanvas .timeline-content {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #f3f4f6;
    }

    #employerHelpOffcanvas .guide-list {
        list-style: none;
        padding: 0;
        margin-bottom: 0;
    }

    #employerHelpOffcanvas .guide-list li {
        margin-bottom: 8px;
        position: relative;
        padding-left: 20px;
    }

    #employerHelpOffcanvas .guide-list li::before {
        content: '\f058';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 2px;
        color: #8b5cf6;
        font-size: 0.8rem;
    }

    #employerHelpOffcanvas .bg-soft-warning {
        background-color: #fffbeb;
        color: #b45309;
    }
</style>