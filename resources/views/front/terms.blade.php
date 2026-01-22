@extends('front.layouts.app')

@section('title', 'Terms and Conditions - TugmaJobs')

@section('content')
<section class="terms-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="terms-title">Terms and Conditions</h1>
                <p class="terms-subtitle">Please read these terms carefully before using TugmaJobs</p>
                <p class="terms-date">Last Updated: {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>
</section>

<section class="terms-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="terms-card">
                    <!-- Table of Contents -->
                    <div class="terms-toc">
                        <h3><i class="fas fa-list-ul me-2"></i>Table of Contents</h3>
                        <ul>
                            <li><a href="#acceptance">1. Acceptance of Terms</a></li>
                            <li><a href="#definitions">2. Definitions</a></li>
                            <li><a href="#account">3. Account Registration</a></li>
                            <li><a href="#user-types">4. User Types and Responsibilities</a></li>
                            <li><a href="#services">5. Services Description</a></li>
                            <li><a href="#prohibited">6. Prohibited Activities</a></li>
                            <li><a href="#content">7. User Content</a></li>
                            <li><a href="#privacy">8. Privacy and Data Protection</a></li>
                            <li><a href="#kyc">9. Identity Verification (KYC)</a></li>
                            <li><a href="#intellectual">10. Intellectual Property</a></li>
                            <li><a href="#disclaimer">11. Disclaimers</a></li>
                            <li><a href="#limitation">12. Limitation of Liability</a></li>
                            <li><a href="#termination">13. Termination</a></li>
                            <li><a href="#changes">14. Changes to Terms</a></li>
                            <li><a href="#contact">15. Contact Information</a></li>
                        </ul>
                    </div>

                    <!-- Section 1: Acceptance of Terms -->
                    <div class="terms-section" id="acceptance">
                        <h2><span class="section-number">1.</span> Acceptance of Terms</h2>
                        <p>By accessing or using TugmaJobs ("the Platform"), you agree to be bound by these Terms and Conditions ("Terms"). If you do not agree to all the terms and conditions of this agreement, you may not access the Platform or use any services.</p>
                        <p>These Terms apply to all visitors, users, and others who access or use the Platform, including but not limited to job seekers, employers, and administrators.</p>
                    </div>

                    <!-- Section 2: Definitions -->
                    <div class="terms-section" id="definitions">
                        <h2><span class="section-number">2.</span> Definitions</h2>
                        <ul class="terms-list">
                            <li><strong>"Platform"</strong> refers to the TugmaJobs website, mobile applications, and all related services.</li>
                            <li><strong>"User"</strong> refers to any individual or entity that accesses or uses the Platform.</li>
                            <li><strong>"Job Seeker"</strong> refers to a User who uses the Platform to search for and apply to job opportunities.</li>
                            <li><strong>"Employer"</strong> refers to a User who uses the Platform to post job listings and recruit candidates.</li>
                            <li><strong>"Content"</strong> refers to any information, data, text, images, or other materials uploaded, posted, or transmitted through the Platform.</li>
                            <li><strong>"Services"</strong> refers to all features and functionalities provided by TugmaJobs.</li>
                        </ul>
                    </div>

                    <!-- Section 3: Account Registration -->
                    <div class="terms-section" id="account">
                        <h2><span class="section-number">3.</span> Account Registration</h2>
                        <p>To access certain features of the Platform, you must register for an account. When creating your account, you agree to:</p>
                        <ul class="terms-list">
                            <li>Provide accurate, current, and complete information during registration</li>
                            <li>Maintain and promptly update your account information</li>
                            <li>Maintain the security of your password and accept all risks of unauthorized access</li>
                            <li>Notify us immediately if you discover or suspect any security breaches</li>
                            <li>Not share your account credentials with any third party</li>
                            <li>Be responsible for all activities that occur under your account</li>
                        </ul>
                        <div class="terms-highlight">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>You must be at least 18 years old to create an account and use our services.</p>
                        </div>
                    </div>

                    <!-- Section 4: User Types and Responsibilities -->
                    <div class="terms-section" id="user-types">
                        <h2><span class="section-number">4.</span> User Types and Responsibilities</h2>

                        <h3>4.1 Job Seekers</h3>
                        <p>As a Job Seeker, you agree to:</p>
                        <ul class="terms-list">
                            <li>Provide truthful and accurate information in your profile and applications</li>
                            <li>Apply only to positions for which you are genuinely interested and qualified</li>
                            <li>Respond professionally to employer communications</li>
                            <li>Not misrepresent your qualifications, experience, or identity</li>
                            <li>Keep your resume and profile information up to date</li>
                            <li>Respect employer confidentiality regarding job details and communications</li>
                        </ul>

                        <h3>4.2 Employers</h3>
                        <p>As an Employer, you agree to:</p>
                        <ul class="terms-list">
                            <li>Post only legitimate job opportunities with accurate descriptions</li>
                            <li>Provide truthful information about your company and job positions</li>
                            <li>Comply with all applicable employment laws and regulations</li>
                            <li>Not discriminate against applicants based on protected characteristics</li>
                            <li>Protect the confidentiality of applicant information</li>
                            <li>Respond to applicants in a timely and professional manner</li>
                            <li>Not use the Platform for any fraudulent or deceptive purposes</li>
                            <li>Verify your business through our KYC process when required</li>
                        </ul>
                    </div>

                    <!-- Section 5: Services Description -->
                    <div class="terms-section" id="services">
                        <h2><span class="section-number">5.</span> Services Description</h2>
                        <p>TugmaJobs provides a platform connecting job seekers with employers in Sta. Cruz, Davao del Sur and surrounding areas. Our services include:</p>
                        <ul class="terms-list">
                            <li><strong>Job Search and Discovery:</strong> Browse and search for job opportunities based on location, category, and keywords</li>
                            <li><strong>Job Posting:</strong> Employers can post job listings with detailed descriptions and requirements</li>
                            <li><strong>Application Management:</strong> Submit and track job applications through our platform</li>
                            <li><strong>Profile Management:</strong> Create and maintain professional profiles for job seekers and company profiles for employers</li>
                            <li><strong>Notifications:</strong> Receive updates about new jobs, application status, and relevant opportunities</li>
                            <li><strong>Analytics:</strong> Access insights and analytics about job market trends and application performance</li>
                            <li><strong>Resume Builder:</strong> Create professional resumes using our built-in tools</li>
                        </ul>
                    </div>

                    <!-- Section 6: Prohibited Activities -->
                    <div class="terms-section" id="prohibited">
                        <h2><span class="section-number">6.</span> Prohibited Activities</h2>
                        <p>You agree NOT to engage in any of the following activities:</p>
                        <ul class="terms-list">
                            <li>Posting false, misleading, or fraudulent job listings</li>
                            <li>Collecting or harvesting user information without consent</li>
                            <li>Using the Platform for spam, phishing, or other malicious purposes</li>
                            <li>Attempting to gain unauthorized access to other accounts or systems</li>
                            <li>Uploading malware, viruses, or harmful code</li>
                            <li>Circumventing or disabling security features of the Platform</li>
                            <li>Using automated tools to scrape or extract data from the Platform</li>
                            <li>Impersonating another person or entity</li>
                            <li>Posting content that is illegal, harmful, threatening, abusive, or discriminatory</li>
                            <li>Soliciting money from job seekers for employment opportunities</li>
                            <li>Using the Platform for purposes unrelated to employment</li>
                        </ul>
                        <div class="terms-warning">
                            <i class="fas fa-ban"></i>
                            <p>Violation of these prohibitions may result in immediate account termination and potential legal action.</p>
                        </div>
                    </div>

                    <!-- Section 7: User Content -->
                    <div class="terms-section" id="content">
                        <h2><span class="section-number">7.</span> User Content</h2>
                        <p>You retain ownership of any content you submit to the Platform. However, by submitting content, you grant TugmaJobs a non-exclusive, worldwide, royalty-free license to use, display, and distribute such content for the purpose of providing our services.</p>
                        <p>You represent and warrant that:</p>
                        <ul class="terms-list">
                            <li>You own or have the necessary rights to the content you submit</li>
                            <li>Your content does not violate any third-party rights</li>
                            <li>Your content is accurate and not misleading</li>
                            <li>Your content complies with all applicable laws and regulations</li>
                        </ul>
                        <p>We reserve the right to remove any content that violates these Terms or that we deem inappropriate at our sole discretion.</p>
                    </div>

                    <!-- Section 8: Privacy and Data Protection -->
                    <div class="terms-section" id="privacy">
                        <h2><span class="section-number">8.</span> Privacy and Data Protection</h2>
                        <p>Your privacy is important to us. Our collection, use, and protection of your personal information is governed by our Privacy Policy, which is incorporated into these Terms by reference.</p>
                        <p>By using TugmaJobs, you consent to:</p>
                        <ul class="terms-list">
                            <li>The collection and processing of your personal data as described in our Privacy Policy</li>
                            <li>The sharing of your profile information with potential employers when you apply for jobs</li>
                            <li>Receiving communications related to your account and job applications</li>
                            <li>The use of cookies and similar technologies as described in our Cookie Policy</li>
                        </ul>
                        <p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>
                    </div>

                    <!-- Section 9: Identity Verification (KYC) -->
                    <div class="terms-section" id="kyc">
                        <h2><span class="section-number">9.</span> Identity Verification (KYC)</h2>
                        <p>To ensure the safety and integrity of our platform, we may require users to complete identity verification (Know Your Customer or KYC) process.</p>
                        <h3>9.1 Verification Requirements</h3>
                        <ul class="terms-list">
                            <li>Job seekers may be required to verify their identity before applying to certain jobs</li>
                            <li>Employers may be required to verify their business credentials before posting job listings</li>
                            <li>Verification may include submission of government-issued ID and other documents</li>
                        </ul>
                        <h3>9.2 Your Responsibilities</h3>
                        <ul class="terms-list">
                            <li>Provide accurate and authentic documents for verification</li>
                            <li>Not use fraudulent or forged documents</li>
                            <li>Complete the verification process in a timely manner when requested</li>
                        </ul>
                        <div class="terms-highlight">
                            <i class="fas fa-shield-alt"></i>
                            <p>Verified accounts help build trust and security for all users on our platform.</p>
                        </div>
                    </div>

                    <!-- Section 10: Intellectual Property -->
                    <div class="terms-section" id="intellectual">
                        <h2><span class="section-number">10.</span> Intellectual Property</h2>
                        <p>The Platform and its original content (excluding user-generated content), features, and functionality are owned by TugmaJobs and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>
                        <p>You may not:</p>
                        <ul class="terms-list">
                            <li>Copy, modify, or distribute our content without permission</li>
                            <li>Use our trademarks, logos, or branding without authorization</li>
                            <li>Reverse engineer or attempt to extract the source code of the Platform</li>
                            <li>Create derivative works based on our Platform</li>
                        </ul>
                    </div>

                    <!-- Section 11: Disclaimers -->
                    <div class="terms-section" id="disclaimer">
                        <h2><span class="section-number">11.</span> Disclaimers</h2>
                        <p>THE PLATFORM IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS. TUGMAJOBS MAKES NO WARRANTIES, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO:</p>
                        <ul class="terms-list">
                            <li>The accuracy or completeness of job listings or user profiles</li>
                            <li>The quality, suitability, or reliability of users or their content</li>
                            <li>Uninterrupted, secure, or error-free operation of the Platform</li>
                            <li>The outcome of any job application or hiring process</li>
                        </ul>
                        <p>TugmaJobs does not guarantee employment for job seekers or successful hiring for employers. We serve only as a platform to connect parties and are not responsible for any decisions made by users.</p>
                    </div>

                    <!-- Section 12: Limitation of Liability -->
                    <div class="terms-section" id="limitation">
                        <h2><span class="section-number">12.</span> Limitation of Liability</h2>
                        <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, TUGMAJOBS SHALL NOT BE LIABLE FOR:</p>
                        <ul class="terms-list">
                            <li>Any indirect, incidental, special, consequential, or punitive damages</li>
                            <li>Loss of profits, revenue, data, or goodwill</li>
                            <li>Any damages arising from user interactions or transactions</li>
                            <li>Any unauthorized access to or alteration of your data</li>
                            <li>Any third-party conduct or content on the Platform</li>
                        </ul>
                        <p>Our total liability shall not exceed the amount you have paid us (if any) in the twelve (12) months preceding the claim.</p>
                    </div>

                    <!-- Section 13: Termination -->
                    <div class="terms-section" id="termination">
                        <h2><span class="section-number">13.</span> Termination</h2>
                        <p>We may terminate or suspend your account and access to the Platform immediately, without prior notice or liability, for any reason, including but not limited to:</p>
                        <ul class="terms-list">
                            <li>Breach of these Terms</li>
                            <li>Fraudulent or illegal activity</li>
                            <li>Providing false information</li>
                            <li>Harassment or abuse of other users</li>
                            <li>Request from law enforcement or government agencies</li>
                        </ul>
                        <p>You may also terminate your account at any time by contacting us or using the account deletion feature in your settings. Upon termination, your right to use the Platform will immediately cease.</p>
                    </div>

                    <!-- Section 14: Changes to Terms -->
                    <div class="terms-section" id="changes">
                        <h2><span class="section-number">14.</span> Changes to Terms</h2>
                        <p>We reserve the right to modify or replace these Terms at any time at our sole discretion. If we make material changes, we will provide notice through:</p>
                        <ul class="terms-list">
                            <li>Email notification to registered users</li>
                            <li>Prominent notice on the Platform</li>
                            <li>Update to the "Last Updated" date at the top of these Terms</li>
                        </ul>
                        <p>Your continued use of the Platform after any such changes constitutes your acceptance of the new Terms.</p>
                    </div>

                    <!-- Section 15: Contact Information -->
                    <div class="terms-section" id="contact">
                        <h2><span class="section-number">15.</span> Contact Information</h2>
                        <p>If you have any questions about these Terms and Conditions, please contact us:</p>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>Email</strong>
                                    <p>support@tugmajobs.com</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong>Address</strong>
                                    <p>Sta. Cruz, Davao del Sur, Philippines</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Business Hours</strong>
                                    <p>Monday - Friday, 8:00 AM - 5:00 PM (PHT)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acceptance Statement -->
                    <div class="terms-acceptance">
                        <p>By using TugmaJobs, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Terms Hero Section */
.terms-hero {
    background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
    padding: 120px 0 60px;
    color: white;
    position: relative;
    overflow: hidden;
}

.terms-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg,
        rgba(67, 86, 99, 0.95) 0%,
        rgba(67, 86, 99, 0.95) 100%);
}

.terms-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 1rem;
    position: relative;
    color: #78C841;
}

.terms-subtitle {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0.5rem;
    position: relative;
}

.terms-date {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.7);
    position: relative;
}

/* Terms Content Section */
.terms-content {
    padding: 60px 0 100px;
    background: #f8fafc;
}

.terms-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    padding: 40px 50px;
    margin-top: -40px;
    position: relative;
}

/* Table of Contents */
.terms-toc {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 40px;
    border: 1px solid #bbf7d0;
}

.terms-toc h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #166534;
    margin-bottom: 20px;
}

.terms-toc ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 10px;
}

.terms-toc li {
    margin: 0;
}

.terms-toc a {
    color: #15803d;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 8px;
    display: block;
    transition: all 0.3s ease;
}

.terms-toc a:hover {
    background: rgba(22, 101, 52, 0.1);
    color: #166534;
}

/* Terms Sections */
.terms-section {
    margin-bottom: 40px;
    padding-bottom: 40px;
    border-bottom: 1px solid #e2e8f0;
}

.terms-section:last-of-type {
    border-bottom: none;
}

.terms-section h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-number {
    background: linear-gradient(135deg, #78C841 0%, #5fb32e 100%);
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
}

.terms-section h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #334155;
    margin: 25px 0 15px;
}

.terms-section p {
    font-size: 1rem;
    line-height: 1.8;
    color: #475569;
    margin-bottom: 15px;
}

/* Terms List */
.terms-list {
    list-style: none;
    padding: 0;
    margin: 15px 0;
}

.terms-list li {
    position: relative;
    padding-left: 28px;
    margin-bottom: 12px;
    font-size: 1rem;
    line-height: 1.7;
    color: #475569;
}

.terms-list li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 10px;
    width: 8px;
    height: 8px;
    background: #78C841;
    border-radius: 50%;
}

.terms-list li strong {
    color: #1e293b;
}

/* Highlight Box */
.terms-highlight {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 4px solid #3b82f6;
    padding: 20px 25px;
    border-radius: 0 12px 12px 0;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin: 20px 0;
}

.terms-highlight i {
    color: #3b82f6;
    font-size: 1.25rem;
    margin-top: 2px;
}

.terms-highlight p {
    margin: 0;
    color: #1e40af;
    font-weight: 500;
}

/* Warning Box */
.terms-warning {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-left: 4px solid #ef4444;
    padding: 20px 25px;
    border-radius: 0 12px 12px 0;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin: 20px 0;
}

.terms-warning i {
    color: #ef4444;
    font-size: 1.25rem;
    margin-top: 2px;
}

.terms-warning p {
    margin: 0;
    color: #b91c1c;
    font-weight: 500;
}

/* Contact Info */
.contact-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 25px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.contact-item i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #78C841 0%, #5fb32e 100%);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.contact-item div strong {
    display: block;
    color: #1e293b;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.contact-item div p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

/* Acceptance Statement */
.terms-acceptance {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 16px;
    padding: 30px;
    text-align: center;
    margin-top: 40px;
    border: 1px solid #bbf7d0;
}

.terms-acceptance p {
    font-size: 1.125rem;
    font-weight: 500;
    color: #166534;
    margin-bottom: 20px;
}

.terms-acceptance .btn {
    background: linear-gradient(135deg, #78C841 0%, #5fb32e 100%);
    border: none;
    padding: 14px 30px;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.terms-acceptance .btn:hover {
    background: linear-gradient(135deg, #5fb32e 0%, #4a9e1f 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(120, 200, 65, 0.3);
}

/* Smooth Scroll */
html {
    scroll-behavior: smooth;
}

/* Responsive Design */
@media (max-width: 768px) {
    .terms-hero {
        padding: 100px 0 40px;
    }

    .terms-title {
        font-size: 2rem;
    }

    .terms-card {
        padding: 25px 20px;
        margin-top: -20px;
    }

    .terms-toc ul {
        grid-template-columns: 1fr;
    }

    .terms-section h2 {
        font-size: 1.25rem;
    }

    .contact-info {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('.terms-toc a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const navbarHeight = 80;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>
@endsection
