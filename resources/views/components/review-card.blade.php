<div class="review-card mb-3">
    <div class="review-header">
        <div class="d-flex align-items-start justify-content-between">
            <div class="d-flex align-items-center">
                <div class="reviewer-avatar">
                    @if($review->is_anonymous)
                        <i class="fas fa-user-secret"></i>
                    @else
                        {{ substr($review->user->name, 0, 1) }}
                    @endif
                </div>
                <div class="ms-3">
                    <h5 class="reviewer-name mb-1">
                        @if($review->is_anonymous)
                            Anonymous User
                        @else
                            {{ $review->user->name }}
                        @endif
                        
                        <!-- Verification Badges -->
                        <span class="badge bg-success ms-2">
                            <i class="fas fa-check-circle"></i> Verified Applicant
                        </span>
                        
                        @if($review->is_verified_hire)
                            <span class="badge bg-primary ms-1">
                                <i class="fas fa-briefcase"></i> Verified Hire
                            </span>
                        @endif
                        
                        @if($review->user->jobSeekerProfile && $review->user->jobSeekerProfile->is_kyc_verified)
                            <span class="badge bg-info ms-1">
                                <i class="fas fa-id-card"></i> KYC Verified
                            </span>
                        @endif
                    </h5>
                    <div class="review-meta">
                        <span class="review-date">
                            <i class="far fa-clock me-1"></i>
                            {{ $review->created_at->diffForHumans() }}
                        </span>
                        @if($review->review_type === 'company' && $review->job)
                            <span class="review-job ms-3">
                                <i class="fas fa-briefcase me-1"></i>
                                Applied for: {{ $review->job->title }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Star Rating -->
            <div class="star-rating">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $review->rating)
                        <i class="fas fa-star text-warning"></i>
                    @else
                        <i class="far fa-star text-muted"></i>
                    @endif
                @endfor
            </div>
        </div>
    </div>
    
    <div class="review-body">
        <h6 class="review-title">{{ $review->title }}</h6>
        <p class="review-comment">{{ $review->comment }}</p>
    </div>
    
    @if($review->employer_response)
        <div class="employer-response">
            <div class="response-header">
                <i class="fas fa-reply me-2"></i>
                <strong>Employer Response</strong>
                <span class="text-muted ms-2">
                    {{ $review->employer_responded_at->diffForHumans() }}
                </span>
            </div>
            <p class="response-text mb-0">{{ $review->employer_response }}</p>
        </div>
    @endif
    
    <div class="review-footer">
        <button class="btn btn-sm btn-outline-secondary helpful-btn" data-review-id="{{ $review->id }}" onclick="markHelpful({{ $review->id }})">
            <i class="far fa-thumbs-up me-1"></i>
            Helpful ({{ $review->helpful_count }})
        </button>
        
        @auth
            @if(Auth::id() === $review->user_id)
                <div class="ms-auto">
                    @if($review->created_at->diffInDays(now()) <= 30)
                        <button class="btn btn-sm btn-outline-primary" onclick="editReview({{ $review->id }})">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                    @endif
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteReview({{ $review->id }})">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            @endif
        @endauth
    </div>
</div>

<script>
function markHelpful(reviewId) {
    @guest
        window.location.href = '{{ route("login") }}';
        return;
    @endguest
    
    console.log('Mark helpful:', reviewId);
    // Placeholder for helpful functionality
    if (typeof showToast === 'function') {
        showToast('Helpful feature coming soon!', 'info');
    } else {
        alert('Helpful feature coming soon!');
    }
}
</script>

<style>
.review-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.review-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.reviewer-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
}

.reviewer-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.review-meta {
    font-size: 0.85rem;
    color: #6b7280;
}

.review-date, .review-job {
    display: inline-flex;
    align-items: center;
}

.star-rating {
    font-size: 1.1rem;
}

.review-body {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f3f4f6;
}

.review-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.review-comment {
    color: #4b5563;
    line-height: 1.6;
    margin: 0;
}

.employer-response {
    margin-top: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-left: 3px solid #6366f1;
    border-radius: 8px;
}

.response-header {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    color: #6366f1;
}

.response-text {
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.5;
}

.review-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
}

.helpful-btn {
    border-color: #e5e7eb;
    color: #6b7280;
}

.helpful-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #4b5563;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
}
</style>
