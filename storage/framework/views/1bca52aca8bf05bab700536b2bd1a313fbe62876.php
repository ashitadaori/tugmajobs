

<?php $__env->startSection('title', 'Document Verification'); ?>
<?php $__env->startSection('page_title', 'Employer Documents'); ?>

<?php $__env->startSection('content'); ?>
<!-- CACHE BUSTER: <?php echo e(now()); ?> -->
<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">
            <!-- VISIBLE PAGE TITLE -->
            <div class="mb-4 p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h2 class="text-white mb-0 fw-bold">
                    <i class="bi bi-file-earmark-text me-3"></i>Employer Documents
                </h2>
                <p class="text-white mb-0 mt-2 opacity-75">Manage your verification documents</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Document Verification</h4>
                    <p class="text-muted mb-0">Upload required documents to complete your employer verification</p>
                </div>
                <a href="<?php echo e(route('employer.documents.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Upload Document
                </a>
            </div>

            <!-- Verification Progress -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="verification-progress-card documents-card">
                        <div class="card-header">
                            <h6>Verification Progress</h6>
                        </div>
                        <div class="card-body">
                            
                            <!-- KYC Status -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <?php if(auth()->user()->isKycVerified()): ?>
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    <?php else: ?>
                                        <i class="fas fa-clock text-warning fa-lg"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium">KYC Verification</span>
                                        <?php if(auth()->user()->isKycVerified()): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="startInlineVerification(event)">
                                                <i class="fas fa-shield-alt me-1"></i>Complete KYC
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">Identity verification using official documents</small>
                                </div>
                            </div>

                            <hr>

                            <!-- Documents Status -->
                            <?php
                                $requiredTypes = collect(\App\Models\EmployerDocument::getDocumentTypes())
                                    ->filter(fn($config) => $config['required']);
                                $completedRequired = 0;
                            ?>

                            <?php $__currentLoopData = $requiredTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $document = $documentsByType->get($type, collect())->first();
                                    $isCompleted = $document && $document->isApproved();
                                    if ($isCompleted) $completedRequired++;
                                ?>
                                
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3">
                                        <?php if($isCompleted): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php elseif($document && $document->isPending()): ?>
                                            <i class="fas fa-clock text-warning"></i>
                                        <?php elseif($document && $document->isRejected()): ?>
                                            <i class="fas fa-times-circle text-danger"></i>
                                        <?php else: ?>
                                            <i class="fas fa-upload text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-medium"><?php echo e($config['label']); ?></span>
                                            <?php if($isCompleted): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif($document && $document->isPending()): ?>
                                                <span class="badge bg-warning">Under Review</span>
                                            <?php elseif($document && $document->isRejected()): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php else: ?>
                                                <span class="status-badge not-uploaded">
                                                    <i class="fas fa-upload"></i>
                                                    Not Uploaded
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <hr>

                            <!-- Overall Status -->
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php if(auth()->user()->canPostJobs()): ?>
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Job Posting Status</span>
                                        <?php if(auth()->user()->canPostJobs()): ?>
                                            <span class="badge bg-success fs-6">Enabled</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning fs-6">Pending Verification</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php if(auth()->user()->canPostJobs()): ?>
                                            You can now post job openings on our platform
                                        <?php else: ?>
                                            Complete KYC and document verification to post jobs
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents List -->
            <div class="row">
                <div class="col-12">
                    <div class="documents-card">
                        <div class="card-header">
                            <h6>Uploaded Documents</h6>
                        </div>
                        <div class="card-body">
                            <?php if($documents->isEmpty()): ?>
                                <div class="empty-state-enhanced">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-file-upload"></i>
                                    </div>
                                    <div class="empty-state-title">No documents uploaded yet</div>
                                    <div class="empty-state-description">
                                        Upload your business documents to complete verification and unlock job posting features
                                    </div>
                                    <a href="<?php echo e(route('employer.documents.create')); ?>" class="empty-state-action">
                                        <i class="fas fa-plus"></i>
                                        Upload First Document
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="documents-table table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Document Type</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Submitted</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <div class="document-type-cell">
                                                            <div class="document-type-icon">
                                                                <i class="fas fa-file-alt"></i>
                                                            </div>
                                                            <div class="document-info">
                                                                <div class="document-name"><?php echo e($document->document_type_config['label'] ?? ucfirst(str_replace('_', ' ', $document->document_type))); ?></div>
                                                                <?php if($document->document_type_config['required'] ?? false): ?>
                                                                    <div class="required-label">
                                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                                        Required
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="document-info">
                                                            <div class="document-name"><?php echo e($document->document_name); ?></div>
                                                            <div class="document-meta"><?php echo e($document->formatted_file_size); ?></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if($document->isApproved()): ?>
                                                            <span class="status-badge approved">
                                                                <i class="fas fa-check-circle"></i>
                                                                Approved
                                                            </span>
                                                        <?php elseif($document->isPending()): ?>
                                                            <span class="status-badge pending">
                                                                <i class="fas fa-clock"></i>
                                                                Under Review
                                                            </span>
                                                        <?php elseif($document->isRejected()): ?>
                                                            <span class="status-badge rejected">
                                                                <i class="fas fa-times-circle"></i>
                                                                Rejected
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="status-badge not-uploaded">
                                                                <i class="fas fa-upload"></i>
                                                                <?php echo e($document->status_label); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="date-cell">
                                                            <div class="date-main"><?php echo e($document->submitted_at->format('M d, Y')); ?></div>
                                                            <div class="date-time"><?php echo e($document->submitted_at->format('g:i A')); ?></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="<?php echo e(route('employer.documents.show', $document)); ?>" 
                                                               class="btn btn-outline-primary" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="<?php echo e(route('employer.documents.download', $document)); ?>" 
                                                               class="btn btn-outline-success" title="Download">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <?php if($document->isRejected()): ?>
                                                                <a href="<?php echo e(route('employer.documents.edit', $document)); ?>" 
                                                                   class="btn btn-outline-warning" title="Resubmit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if(!$document->isApproved()): ?>
                                                                <button type="button" class="btn btn-outline-danger" 
                                                                        onclick="deleteDocument(<?php echo e($document->id); ?>)" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php if($document->isRejected() && $document->admin_notes): ?>
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="alert alert-warning mb-0 ms-4">
                                                                <strong>Admin Notes:</strong> <?php echo e($document->admin_notes); ?>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this document? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- KYC Inline Verification Script -->
<script>
    // Set current user ID for KYC polling
    window.currentUserId = <?php echo e(auth()->id()); ?>;
</script>
<script src="<?php echo e(asset('assets/js/kyc-inline-verification.js')); ?>"></script>

<script>
function deleteDocument(documentId) {
    const form = document.getElementById('deleteForm');
    form.action = `/employer/documents/${documentId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.employer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/employer/documents/index.blade.php ENDPATH**/ ?>