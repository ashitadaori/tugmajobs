

<?php $__env->startSection('page_title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-0">
    <!-- Filter Section -->
    <div class="filter-section mb-4">
        <div class="card">
            <div class="card-body">
                <form action="<?php echo e(route('admin.users.index')); ?>" method="GET" id="filter-form">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-md-2">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search users..."
                                   value="<?php echo e(request('search')); ?>">
                        </div>

                        <!-- Role Filter -->
                        <div class="col-md-2">
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="jobseeker" <?php echo e(request('role') === 'jobseeker' ? 'selected' : ''); ?>>Job Seeker</option>
                                <option value="employer" <?php echo e(request('role') === 'employer' ? 'selected' : ''); ?>>Employer</option>
                                <option value="admin" <?php echo e(request('role') === 'admin' ? 'selected' : ''); ?>>Admin</option>
                            </select>
                        </div>


                        <!-- KYC Status Filter -->
                        <div class="col-md-2">
                            <select name="kyc_status" class="form-select">
                                <option value="all">All KYC Status</option>
                                <option value="verified" <?php echo e(request('kyc_status') === 'verified' ? 'selected' : ''); ?>>KYC Verified</option>
                                <option value="in_progress" <?php echo e(request('kyc_status') === 'in_progress' ? 'selected' : ''); ?>>KYC Pending</option>
                                <option value="rejected" <?php echo e(request('kyc_status') === 'rejected' ? 'selected' : ''); ?>>KYC Rejected</option>
                                <option value="not_started" <?php echo e(request('kyc_status') === 'not_started' ? 'selected' : ''); ?>>KYC Not Started</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="col-md-2">
                            <input type="text" 
                                   name="date_range" 
                                   class="form-control daterangepicker" 
                                   placeholder="Registration Date Range"
                                   value="<?php echo e(request('date_range')); ?>">
                        </div>

                        <!-- Filter Button -->
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stats-card">
                <h6 class="card-title">Total Users</h6>
                <h3 class="mb-0"><?php echo e($counts['total']); ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card">
                <h6 class="card-title">Verified</h6>
                <h3 class="mb-0"><?php echo e($counts['verified']); ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card">
                <h6 class="card-title">Unverified</h6>
                <h3 class="mb-0"><?php echo e($counts['unverified']); ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card">
                <h6 class="card-title">Employers</h6>
                <h3 class="mb-0"><?php echo e($counts['employers']); ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card">
                <h6 class="card-title">Job Seekers</h6>
                <h3 class="mb-0"><?php echo e($counts['jobseekers']); ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card">
                <h6 class="card-title">Admins</h6>
                <h3 class="mb-0"><?php echo e($counts['admins']); ?></h3>
            </div>
        </div>
    </div>

    <!-- KYC Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stats-card bg-success text-white">
                <h6 class="card-title">KYC Verified</h6>
                <h3 class="mb-0"><?php echo e($counts['kyc_verified']); ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stats-card bg-warning text-white">
                <h6 class="card-title">KYC Pending</h6>
                <h3 class="mb-0"><?php echo e($counts['kyc_pending']); ?></h3>
            </div>
        </div>
    </div>

    <!-- Content Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Users List</h5>
            <a href="<?php echo e(route('admin.users.export', request()->query())); ?>" 
               class="btn btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
        <div class="card-body">
            <!-- Bulk Actions -->
            <div class="bulk-actions mb-3">
                <form id="bulk-action-form" class="d-flex gap-2">
                    <select class="form-select w-auto" name="action" required>
                        <option value="">Bulk Actions</option>
                        <option value="suspend">Suspend Selected</option>
                        <option value="unsuspend">Unsuspend Selected</option>
                        <option value="force-kyc">Force KYC Reverification</option>
                        <?php if(auth()->user()->role === 'superadmin'): ?>
                            <option value="delete">Delete Selected</option>
                        <?php endif; ?>
                    </select>
                    <button type="submit" class="btn btn-primary" id="bulk-action-btn" disabled>
                        Apply
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>
                                <a href="<?php echo e(route('admin.users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']))); ?>" 
                                   class="text-decoration-none text-dark">
                                    Name
                                    <?php if(request('sort') === 'name'): ?>
                                        <i class="bi bi-sort-<?php echo e(request('direction') === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo e(route('admin.users.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']))); ?>"
                                   class="text-decoration-none text-dark">
                                    Email
                                    <?php if(request('sort') === 'email'): ?>
                                        <i class="bi bi-sort-<?php echo e(request('direction') === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Role</th>
                            <th>KYC Status</th>
                            <th>
                                <a href="<?php echo e(route('admin.users.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']))); ?>"
                                   class="text-decoration-none text-dark">
                                    Registered
                                    <?php if(request('sort') === 'created_at' || !request('sort')): ?>
                                        <i class="bi bi-sort-<?php echo e(request('direction', 'desc') === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       class="form-check-input user-checkbox" 
                                       value="<?php echo e($user->id); ?>"
                                       <?php echo e($user->id === auth()->id() ? 'disabled' : ''); ?>>
                            </td>
                            <td><?php echo e($user->name); ?></td>
                            <td><?php echo e($user->email); ?></td>
                            <td>
                                <span class="badge-custom <?php echo e($user->role === 'admin' ? 'badge-active' : ($user->role === 'employer' ? 'badge-pending' : 'badge-custom')); ?>">
                                    <?php echo e(ucfirst($user->role)); ?>

                                </span>
                            </td>
                            <td>
                                <?php
                                    $kycStatusColor = match($user->kyc_status) {
                                        'verified' => 'success',
                                        'in_progress' => 'warning',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>
                                <span class="badge bg-<?php echo e($kycStatusColor); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $user->kyc_status))); ?>

                                </span>
                            </td>
                            <td><?php echo e($user->created_at->format('M d, Y')); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    
                                    <?php if($user->role === 'jobseeker' && $user->jobSeekerProfile && $user->jobSeekerProfile->resume_file): ?>
                                        <a href="<?php echo e(asset('storage/resumes/' . $user->jobSeekerProfile->resume_file)); ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           target="_blank"
                                           title="Download Resume">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    
                                    <a href="<?php echo e(route('admin.users.edit', $user->id)); ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($users->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize date range picker
    $('.daterangepicker').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('.daterangepicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('.daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Select all checkbox
    $('#select-all').change(function() {
        $('.user-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
        updateBulkActionButton();
    });

    // Individual checkboxes
    $('.user-checkbox').change(function() {
        updateBulkActionButton();
    });

    function updateBulkActionButton() {
        const checkedCount = $('.user-checkbox:checked').length;
        $('#bulk-action-btn').prop('disabled', checkedCount === 0);
    }

    // Bulk actions
    $('#bulk-action-form').submit(function(e) {
        e.preventDefault();
        
        const action = $('select[name="action"]').val();
        if (!action) {
            alert('Please select an action');
            return;
        }

        const userIds = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (userIds.length === 0) {
            alert('Please select at least one user');
            return;
        }

        if (action === 'delete' && !confirm('Are you sure you want to delete the selected users?')) {
            return;
        }

        $.ajax({
            url: '<?php echo e(route("admin.users.bulk-action")); ?>',
            type: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                action: action,
                user_ids: userIds
            },
            success: function(response) {
                if (response.status) {
                    window.location.reload();
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Suspend/Unsuspend buttons
    $('.suspend-btn, .unsuspend-btn').click(function() {
        const userId = $(this).data('user-id');
        const action = $(this).hasClass('suspend-btn') ? 'suspend' : 'unsuspend';
        const url = `/admin/users/${userId}/${action}`;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>'
            },
            success: function(response) {
                if (response.status) {
                    window.location.reload();
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Delete confirmation
    $('.delete-form').submit(function(e) {
        if (!confirm('Are you sure you want to delete this user?')) {
            e.preventDefault();
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/users/list.blade.php ENDPATH**/ ?>