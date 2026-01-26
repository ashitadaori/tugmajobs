@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Privacy Policy</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h1 class="h3 mb-4">Privacy Policy</h1>

                            <div class="policy-content">
                                <p class="text-muted mb-4">Last Updated: {{ date('F d, Y') }}</p>

                                <h4 class="h5 mt-4">1. Information We Collect</h4>
                                <p>We collect information you provide directly to us when you create an account, update your
                                    profile, apply for jobs, or communicate with us. This may include your name, email
                                    address, phone number, employment history, and resume/CV.</p>

                                <h4 class="h5 mt-4">2. How We Use Your Information</h4>
                                <p>We use the information we collect to:</p>
                                <ul>
                                    <li>Provide, maintain, and improve our services</li>
                                    <li>Process job applications and facilitate communication between job seekers and
                                        employers</li>
                                    <li>Send you technical notices, updates, and support messages</li>
                                    <li>Monitor and analyze trends and usage</li>
                                </ul>

                                <h4 class="h5 mt-4">3. Information Sharing</h4>
                                <p>We share your information with employers when you apply for jobs. profiles are visible to
                                    employers based on your privacy settings. We do not sell your personal information to
                                    third parties.</p>

                                <h4 class="h5 mt-4">4. Data Security</h4>
                                <p>We implement appropriate technical and organizational measures to protect your personal
                                    information against unauthorized access, alteration, disclosure, or destruction.</p>

                                <h4 class="h5 mt-4">5. Your Rights</h4>
                                <p>You have the right to access, correct, or delete your personal information. You can
                                    manage your profile settings through your account dashboard.</p>

                                <h4 class="h5 mt-4">6. Contact Us</h4>
                                <p>If you have any questions about this Privacy Policy, please contact us at
                                    support@tugmajobs.com.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection