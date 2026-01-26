@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Cookie Policy</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h1 class="h3 mb-4">Cookie Policy</h1>

                            <div class="policy-content">
                                <p class="text-muted mb-4">Last Updated: {{ date('F d, Y') }}</p>

                                <h4 class="h5 mt-4">1. What Are Cookies</h4>
                                <p>Cookies are small text files that are stored on your computer or mobile device when you
                                    visit a website. They help us remember your preferences and improve your user
                                    experience.</p>

                                <h4 class="h5 mt-4">2. How We Use Cookies</h4>
                                <p>We use cookies for the following purposes:</p>
                                <ul>
                                    <li><strong>Essential Cookies:</strong> Required for the website to function properly
                                        (e.g., authentication, security).</li>
                                    <li><strong>Functional Cookies:</strong> Remember your preferences and settings.</li>
                                    <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with
                                        our website.</li>
                                </ul>

                                <h4 class="h5 mt-4">3. Managing Cookies</h4>
                                <p>Most web browsers automatically accept cookies, but you can usually modify your browser
                                    settings to decline cookies if you prefer. However, this may prevent you from taking
                                    full advantage of the website.</p>

                                <h4 class="h5 mt-4">4. Third-Party Cookies</h4>
                                <p>We may use third-party services (such as Google Analytics or Mapbox) that set their own
                                    cookies to provide their services. We do not control these cookies.</p>

                                <h4 class="h5 mt-4">5. Updates to This Policy</h4>
                                <p>We may update this Cookie Policy from time to time. We encourage you to review this page
                                    periodically for any changes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection