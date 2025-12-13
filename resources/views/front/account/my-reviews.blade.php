@extends('layouts.jobseeker')

@section('page-title', 'My Reviews')

@section('jobseeker-content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid px-4 py-4">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">My Reviews</h1>
                    <p class="text-muted mb-0">Manage your job and company reviews</p>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="col-12">
            @if($reviews->count() > 0)
                @foreach($reviews as $review)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge {{ $review->review_type === 'job' ? 'bg-primary' : 'bg-info' }} me-2">
                                            {{ ucfirst($review->review_type) }} Review
                                        </span>
                                        @if($review->is_verified_hire)
                                            <span class="badge bg-success me-2">
                                                <i class="fas fa-check-circle me-1"></i>Verified Hire
                                            </span>
                                        @endif
                                        @if($review->is_anonymous)
                                            <span class="badge bg-secondary me-2">
                                                <i class="fas fa-user-secret me-1"></i>Anonymous
                                            </span>
                                        @endif
                                    </div>

                                    <h5 class="card-title mb-1">{{ $review->title }}</h5>

                                    @if($review->job)
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-briefcase me-1"></i>
                                            <a href="{{ route('jobDetail', $review->job->id) }}" class="text-decoration-none">
                                                {{ $review->job->title }}
                                            </a>
                                            @if($review->employer && $review->employer->employerProfile)
                                                at {{ $review->employer->employerProfile->company_name ?? $review->employer->name }}
                                            @endif
                                        </p>
                                    @endif

                                    <!-- Rating Stars -->
                                    <div class="mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                        <span class="ms-2 text-muted">({{ $review->rating }}/5)</span>
                                    </div>

                                    <p class="card-text">{{ $review->comment }}</p>

                                    @if($review->employer_response)
                                        <div class="bg-light p-3 rounded mt-3">
                                            <strong><i class="fas fa-reply me-1"></i>Employer Response:</strong>
                                            <p class="mb-0 mt-2">{{ $review->employer_response }}</p>
                                            @if($review->employer_responded_at)
                                                <small class="text-muted">Responded on {{ $review->employer_responded_at->format('M d, Y') }}</small>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>Posted on {{ $review->created_at->format('M d, Y') }}
                                            @if($review->created_at->diffInDays(now()) <= 30)
                                                <span class="badge bg-light text-dark ms-2">
                                                    <i class="fas fa-edit me-1"></i>Editable
                                                </span>
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <div class="dropdown">
                                    <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if($review->created_at->diffInDays(now()) <= 30)
                                            <li>
                                                <button class="dropdown-item" onclick="editReview({{ $review->id }})">
                                                    <i class="fas fa-edit me-2"></i>Edit Review
                                                </button>
                                            </li>
                                        @endif
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="deleteReview({{ $review->id }})">
                                                <i class="fas fa-trash me-2"></i>Delete Review
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $reviews->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-star fa-4x text-muted mb-3"></i>
                        <h5>No Reviews Yet</h5>
                        <p class="text-muted">You haven't written any reviews yet. Apply to jobs and share your experience!</p>
                        <a href="{{ route('jobs') }}" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Browse Jobs
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editReview(reviewId) {
    // Implement edit review modal/page
    alert('Edit review functionality - Review ID: ' + reviewId);
}

function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        fetch(`{{ url('/account/reviews') }}/${reviewId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete review');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the review');
        });
    }
}
</script>
@endpush
