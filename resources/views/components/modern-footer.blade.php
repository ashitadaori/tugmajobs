<!-- Modern Footer -->
<footer class="modern-footer">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Section -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <h4 class="brand-name">
                        <i class="fas fa-briefcase me-2"></i>
                        TugmaJobs
                    </h4>
                    <p class="brand-description">
                        Search all the open positions on the web. Get your own personalized salary estimate. Read reviews on over 30,000+ companies worldwide.
                    </p>
                </div>
            </div>
            
            <!-- For Jobseekers -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">For Jobseekers</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('jobs') }}" class="footer-link"><span>Browse Jobs</span></a></li>
                    <li><a href="{{ route('companies') }}" class="footer-link"><span>Companies</span></a></li>
                    @guest
                    <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#authModal"><span>Sign Up</span></a></li>
                    @else
                    <li><a href="{{ route('account.dashboard') }}" class="footer-link"><span>My Dashboard</span></a></li>
                    @endguest
                    <li><a href="{{ route('jobs') }}?location=Sta. Cruz" class="footer-link"><span>Jobs in Sta. Cruz</span></a></li>
                    <li><a href="{{ route('jobs') }}?job_type=remote" class="footer-link"><span>Remote Jobs</span></a></li>
                </ul>
            </div>

            <!-- For Employers -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">For Employers</h5>
                <ul class="footer-links">
                    @guest
                    <li><a href="{{ route('employer.register') }}" class="footer-link"><span>Post a Job</span></a></li>
                    <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#employerAuthModal"><span>Employer Sign In</span></a></li>
                    @else
                        @if(Auth::user()->isEmployer())
                        <li><a href="{{ route('employer.dashboard') }}" class="footer-link"><span>Employer Dashboard</span></a></li>
                        <li><a href="{{ route('employer.jobs.create') }}" class="footer-link"><span>Post a Job</span></a></li>
                        @else
                        <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#jobseekerWarningModal"><span>Post a Job</span></a></li>
                        @endif
                    @endguest
                    <li><a href="{{ route('home') }}" class="footer-link"><span>About TugmaJobs</span></a></li>
                    <li><a href="#" class="footer-link"><span>FAQ</span></a></li>
                    <li><a href="#" class="footer-link"><span>Contact Us</span></a></li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-title">Newsletter</h5>
                <p class="newsletter-text">Sign up and receive the latest tips via email.</p>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control newsletter-input" placeholder="Write your email">
                        <button class="btn btn-newsletter" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr class="footer-divider">
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="row align-items-center gy-3">
                <div class="col-md-6">
                    <p class="footer-copyright">
                        © 2025 TugmaJobs. Design with <i class="fas fa-heart text-danger"></i> by <a href="#" class="footer-credit">Tribyte</a>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="footer-tagline">
                        Follow us for updates and job opportunities
                    </p>
                    <div class="footer-social mt-2">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .footer-bottom {
            padding-top: 2rem;
        }
        
        .footer-social {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .social-link {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.125rem;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .social-link:hover {
            color: #4fd1c5;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .footer-social {
                justify-content: center;
                margin-top: 1rem;
            }
            
            .footer-bottom {
                text-align: center;
            }
        }
        </style>
    </div>
</footer>

<style>
/* Reset styles for footer */
.modern-footer *,
.modern-footer *:before,
.modern-footer *:after {
    box-sizing: border-box;
}

/* Main footer styles */
.modern-footer {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    color: #e2e8f0 !important;
    padding: 80px 0 30px;
    position: relative;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Brand section */
.modern-footer .brand-name {
    background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    letter-spacing: -0.5px;
}

.modern-footer .brand-description {
    color: #e2e8f0 !important;
    font-size: 15px;
    line-height: 1.7;
    opacity: 1;
    margin-bottom: 0;
    max-width: 90%;
}

/* Section titles */
.modern-footer .footer-title {
    color: #ffffff !important;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.75rem;
    letter-spacing: 0.5px;
}

/* Links section */
.modern-footer .footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.modern-footer .footer-links li {
    margin-bottom: 0.75rem;
}

.modern-footer .footer-link {
    display: inline-block;
    color: #e2e8f0 !important;
    text-decoration: none !important;
    font-size: 15px;
    font-weight: 400;
    transition: all 0.3s ease;
    opacity: 1;
    padding: 2px 0;
}

.modern-footer .footer-link span {
    position: relative;
    display: inline-block;
}

.modern-footer .footer-link span::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 0;
    background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%);
    transition: width 0.3s ease;
}

.modern-footer .footer-link:hover {
    color: #4fd1c5 !important;
    opacity: 1;
}

.modern-footer .footer-link:hover span::after {
    width: 100%;
}

/* Newsletter section */
.modern-footer .newsletter-text {
    color: #ffffff !important;
    font-size: 15px;
    margin-bottom: 1.5rem;
    font-weight: 400;
    letter-spacing: 0.3px;
    opacity: 0.9;
}

.modern-footer .newsletter-form .input-group {
    background: rgba(255, 255, 255, 0.05);
    padding: 4px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modern-footer .newsletter-input {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 12px 20px;
    font-size: 15px;
    color: #1e293b;
    border-radius: 12px;
    height: 48px;
    transition: all 0.3s ease;
}

.modern-footer .newsletter-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.3);
    border-color: #4fd1c5;
}

.modern-footer .btn-newsletter {
    background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%) !important;
    color: #0f172a !important;
    border: none !important;
    padding: 12px 28px;
    font-weight: 600;
    font-size: 15px;
    border-radius: 12px;
    height: 48px;
    margin-left: 4px;
    white-space: nowrap;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(56, 178, 172, 0.2);
}

.modern-footer .btn-newsletter:hover {
    background: linear-gradient(135deg, #45c1b5 0%, #319c96 100%) !important;
    color: #0f172a !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(56, 178, 172, 0.3);
}

/* Footer bottom section */
.modern-footer .footer-divider {
    border-color: rgba(255, 255, 255, 0.1);
    margin: 2.5rem 0 2rem;
}

.modern-footer .footer-copyright {
    color: #ffffff !important;
    font-size: 15px;
    margin: 0;
    letter-spacing: 0.3px;
    opacity: 0.9;
}

.modern-footer .footer-credit {
    color: #4fd1c5 !important;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.modern-footer .footer-credit:hover {
    color: #ffffff !important;
}

.modern-footer .footer-tagline {
    color: #ffffff !important;
    font-size: 15px;
    margin: 0;
    letter-spacing: 0.3px;
    opacity: 0.9;
}

.modern-footer .footer-bottom {
    padding-top: 2rem;
}

/* Social links */
.modern-footer .footer-social {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1rem;
}

.modern-footer .social-link {
    color: #e2e8f0 !important;
    font-size: 1.125rem;
    text-decoration: none;
    transition: all 0.3s ease;
    padding: 8px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.modern-footer .social-link:hover {
    color: #4fd1c5 !important;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
    border-color: #4fd1c5;
    box-shadow: 0 4px 12px rgba(79, 209, 197, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modern-footer {
        padding: 60px 0 30px;
        text-align: center;
    }
    
    .modern-footer .footer-social {
        justify-content: center;
    }
    
    .modern-footer .footer-bottom {
        text-align: center;
    }
    
    .modern-footer .footer-link:hover {
        transform: none;
    }
    
    .modern-footer .brand-description {
        max-width: 100%;
    }
}
</style>
</style>

<!-- Jobseeker Warning Modal -->
<div class="modal fade" id="jobseekerWarningModal" tabindex="-1" aria-labelledby="jobseekerWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="modal-header border-0 pb-0" style="padding: 1.5rem 1.5rem 0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" style="padding: 1rem 2rem 2rem;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #d97706;"></i>
                </div>
                <h5 class="modal-title mb-3" id="jobseekerWarningModalLabel" style="font-weight: 700; color: #1e293b; font-size: 1.5rem;">Employer Feature Only</h5>
                <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    The "Post a Job" feature is exclusively available for employer accounts. As a jobseeker, you can browse and apply for jobs instead.
                </p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('jobs') }}" class="btn" style="background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%); color: #0f172a; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 12px; border: none;">
                        <i class="fas fa-search me-2"></i>Browse Jobs
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 500;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>