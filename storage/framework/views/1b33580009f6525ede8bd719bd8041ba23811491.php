

<?php $__env->startSection('page_title', 'Reviews & Ratings'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Reviews & Ratings</h1>
            <p class="text-muted">Manage and respond to reviews from jobseekers</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reviews
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['total_reviews']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Average Rating
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php if($stats['avg_rating']): ?>
                                    <?php echo e(number_format($stats['avg_rating'], 1)); ?> ⭐
                                <?php else: ?>
                                    No ratings yet
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Job Reviews
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['job_reviews']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Company Reviews
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['company_reviews']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Reviews</h6>
                <div class="btn-group" role="group">
                    <a href="<?php echo e(route('employer.reviews.index', ['type' => 'all'])); ?>" 
                       class="btn btn-sm <?php echo e($filterType === 'all' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        All Reviews
                    </a>
                    <a href="<?php echo e(route('employer.reviews.index', ['type' => 'job'])); ?>" 
                       class="btn btn-sm <?php echo e($filterType === 'job' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        Job Reviews
                    </a>
                    <a href="<?php echo e(route('employer.reviews.index', ['type' => 'company'])); ?>" 
                       class="btn btn-sm <?php echo e($filterType === 'company' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        Company Reviews
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if($reviews->isEmpty()): ?>
                <div class="text-center py-5">
                    <i class="fas fa-star-half-alt text-muted fa-3x mb-3"></i>
                    <h5>No Reviews Yet</h5>
                    <p class="text-muted">When jobseekers review your jobs or company, they'll appear here.</p>
                </div>
            <?php else: ?>
                <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="review-item border-bottom pb-4 mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Review Header -->
                                <div class="d-flex align-items-center mb-2">
                                    <div class="reviewer-avatar me-3">
                                        <?php if($review->is_anonymous): ?>
                                            <i class="fas fa-user-secret"></i>
                                        <?php else: ?>
                                            <?php echo e(substr($review->user->name, 0, 1)); ?>

                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">
                                            <?php if($review->is_anonymous): ?>
                                                Anonymous User
                                            <?php else: ?>
                                                <?php echo e($review->user->name); ?>

                                            <?php endif; ?>
                                            <span class="badge bg-success ms-2">
                                                <i class="fas fa-check-circle"></i> Verified Applicant
                                            </span>
                                            <?php if($review->is_verified_hire): ?>
                                                <span class="badge bg-primary ms-1">
                                                    <i class="fas fa-briefcase"></i> Verified Hire
                                                </span>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="text-muted small">
                                            <span class="badge bg-<?php echo e($review->review_type === 'job' ? 'info' : 'warning'); ?> me-2">
                                                <?php echo e(ucfirst($review->review_type)); ?> Review
                                            </span>
                                            <?php if($review->job): ?>
                                                Applied for: <?php echo e($review->job->title); ?>

                                            <?php endif; ?>
                                            • <?php echo e($review->created_at->diffForHumans()); ?>

                                        </div>
                                    </div>
                                </div>

                                <!-- Rating -->
                                <div class="mb-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= $review->rating): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-muted"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span class="ms-2 text-muted">(<?php echo e($review->rating); ?>/5)</span>
                                </div>

                                <!-- Review Content -->
                                <h6 class="review-title"><?php echo e($review->title); ?></h6>
                                <p class="review-comment mb-3"><?php echo e($review->comment); ?></p>

                                <!-- Employer Response -->
                                <?php if($review->employer_response): ?>
                                    <div class="employer-response bg-light p-3 rounded mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong class="text-primary">
                                                <i class="fas fa-reply me-1"></i> Your Response
                                            </strong>
                                            <small class="text-muted"><?php echo e($review->employer_responded_at->diffForHumans()); ?></small>
                                        </div>
                                        <p class="mb-0"><?php echo e($review->employer_response); ?></p>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-primary edit-response-btn" 
                                                    data-review-id="<?php echo e($review->id); ?>" 
                                                    data-response="<?php echo e($review->employer_response); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-response-btn" 
                                                    data-review-id="<?php echo e($review->id); ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <?php if(!$review->employer_response): ?>
                                    <!-- Response Form -->
                                    <div class="response-form">
                                        <h6 class="mb-3">Respond to this review</h6>
                                        <form class="response-form-submit" data-review-id="<?php echo e($review->id); ?>">
                                            <?php echo csrf_field(); ?>
                                            <div class="mb-3">
                                                <textarea class="form-control" name="response" rows="4" 
                                                          placeholder="Write a professional response..." 
                                                          required minlength="10" maxlength="1000"></textarea>
                                                <small class="form-text text-muted">10-1000 characters</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-paper-plane me-1"></i> Post Response
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    <?php echo e($reviews->appends(request()->query())->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Response Modal -->
<div class="modal fade" id="editResponseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editResponseForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="editReviewId">
                    <div class="mb-3">
                        <label class="form-label">Your Response</label>
                        <textarea class="form-control" id="editResponseText" rows="4" 
                                  required minlength="10" maxlength="1000"></textarea>
                        <small class="form-text text-muted">10-1000 characters</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateResponseBtn">
                    <i class="fas fa-save me-1"></i> Update Response
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Main container background */
.container-fluid {
    background-color: #ffffff !important;
}

/* Card backgrounds */
.card {
    background-color: #ffffff !important;
}

.card-body {
    background-color: #ffffff !important;
}

/* Card header - white background */
.card-header {
    background-color: #ffffff !important;
    border-bottom: 1px solid #e3e6f0;
}

.reviewer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.review-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.employer-response {
    border-left: 3px solid #6366f1;
    background-color: #f8f9fa !important;
}

.response-form {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

/* Modal background */
.modal-content {
    background-color: #ffffff !important;
}

.modal-header {
    background-color: #ffffff !important;
}

.modal-body {
    background-color: #ffffff !important;
}

.modal-footer {
    background-color: #ffffff !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Submit response
    $('.response-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const reviewId = form.data('review-id');
        const response = form.find('textarea[name="response"]').val();
        const submitBtn = form.find('button[type="submit"]');
        
        // Show loading
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Posting...');
        
        $.ajax({
            url: `/employer/reviews/${reviewId}/respond`,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                response: response
            },
            success: function(data) {
                if (data.status) {
                    location.reload();
                } else {
                    alert(data.message || 'Error posting response');
                }
            },
            error: function() {
                alert('Error posting response. Please try again.');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Post Response');
            }
        });
    });
    
    // Edit response
    $('.edit-response-btn').on('click', function() {
        const reviewId = $(this).data('review-id');
        const currentResponse = $(this).data('response');
        
        $('#editReviewId').val(reviewId);
        $('#editResponseText').val(currentResponse);
        $('#editResponseModal').modal('show');
    });
    
    // Update response
    $('#updateResponseBtn').on('click', function() {
        const reviewId = $('#editReviewId').val();
        const response = $('#editResponseText').val();
        const btn = $(this);
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');
        
        $.ajax({
            url: `/employer/reviews/${reviewId}/response`,
            type: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                response: response
            },
            success: function(data) {
                if (data.status) {
                    location.reload();
                } else {
                    alert(data.message || 'Error updating response');
                }
            },
            error: function() {
                alert('Error updating response. Please try again.');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Response');
            }
        });
    });
    
    // Delete response
    $('.delete-response-btn').on('click', function() {
        if (!confirm('Are you sure you want to delete this response?')) {
            return;
        }
        
        const reviewId = $(this).data('review-id');
        const btn = $(this);
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/employer/reviews/${reviewId}/response`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.status) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting response');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Error deleting response. Please try again.');
                btn.prop('disabled', false);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.employer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/employer/reviews/index.blade.php ENDPATH**/ ?>