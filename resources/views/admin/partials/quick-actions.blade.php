<!-- Quick Actions Floating Widget -->
<div id="quickActionsWidget">
    <!-- Floating Action Button -->
    <button class="quick-actions-fab" id="qaFab" title="Quick Actions">
        <i class="bi bi-lightning-charge"></i>
        <span class="qa-badge" id="qaBadge" style="display: none;">0</span>
    </button>

    <!-- Quick Actions Panel -->
    <div class="quick-actions-panel" id="qaPanel" style="display: none;">
        <div class="qa-header">
            <h6 class="mb-0">
                <i class="bi bi-lightning-charge me-2"></i>Quick Actions
            </h6>
            <button class="qa-close" id="qaClose">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <div class="qa-body">
            <!-- Pending Actions Summary -->
            <div class="qa-summary" id="qaSummary">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading actions...</span>
            </div>

            <!-- Action Tabs -->
            <div class="qa-tabs">
                <button class="qa-tab active" data-tab="jobs">
                    <i class="bi bi-briefcase"></i>
                    <span>Jobs</span>
                    <span class="qa-tab-badge" id="jobsBadge">0</span>
                </button>
                <button class="qa-tab" data-tab="kyc">
                    <i class="bi bi-shield-check"></i>
                    <span>KYC</span>
                    <span class="qa-tab-badge" id="kycBadge">0</span>
                </button>
                <button class="qa-tab" data-tab="email">
                    <i class="bi bi-envelope"></i>
                    <span>Email</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="qa-tab-content">
                <!-- Jobs Tab -->
                <div class="qa-tab-pane active" id="jobsTab">
                    <div class="qa-section">
                        <h6 class="qa-section-title">Pending Jobs</h6>
                        <div id="pendingJobsList" class="qa-list">
                            <div class="text-center text-muted py-3">
                                Loading pending jobs...
                            </div>
                        </div>
                        <div class="qa-actions mt-3">
                            <button class="btn btn-sm btn-success w-100 mb-2" onclick="bulkApproveJobs()" id="bulkApproveBtn" disabled>
                                <i class="bi bi-check-circle me-1"></i>Approve Selected
                            </button>
                            <button class="btn btn-sm btn-warning w-100" onclick="showBulkRejectModal()" id="bulkRejectBtn" disabled>
                                <i class="bi bi-x-circle me-1"></i>Reject Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- KYC Tab -->
                <div class="qa-tab-pane" id="kycTab" style="display: none;">
                    <div class="qa-section">
                        <h6 class="qa-section-title">Pending KYC Verifications</h6>
                        <div id="pendingKycList" class="qa-list">
                            <div class="text-center text-muted py-3">
                                Loading pending verifications...
                            </div>
                        </div>
                        <div class="qa-actions mt-3">
                            <button class="btn btn-sm btn-success w-100" onclick="bulkVerifyKyc()" id="bulkVerifyBtn" disabled>
                                <i class="bi bi-shield-check me-1"></i>Verify Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Email Tab -->
                <div class="qa-tab-pane" id="emailTab" style="display: none;">
                    <div class="qa-section">
                        <h6 class="qa-section-title">Send Mass Email</h6>
                        <form id="massEmailForm">
                            <div class="mb-3">
                                <label class="form-label form-label-sm">Recipient Segment</label>
                                <select class="form-select form-select-sm" id="emailSegment" required>
                                    <option value="all">All Users</option>
                                    <option value="jobseekers">Job Seekers Only</option>
                                    <option value="employers">Employers Only</option>
                                    <option value="verified">Verified Users</option>
                                    <option value="unverified">Unverified Users</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-sm">Subject</label>
                                <input type="text" class="form-control form-control-sm" id="emailSubject" required maxlength="200">
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-sm">Message</label>
                                <textarea class="form-control form-control-sm" id="emailMessage" rows="4" required minlength="20"></textarea>
                                <small class="text-muted">Minimum 20 characters</small>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input" type="checkbox" id="sendEmail" checked>
                                    <label class="form-check-label" for="sendEmail">
                                        Send Email Notification
                                    </label>
                                </div>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input" type="checkbox" id="sendNotification" checked>
                                    <label class="form-check-label" for="sendNotification">
                                        Send In-App Notification
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-send me-1"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Selected Jobs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkRejectForm">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" rows="4" required minlength="10" maxlength="500" placeholder="Please provide a clear reason for rejection (10-500 characters)"></textarea>
                        <small class="text-muted">This will be sent to the employer</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmBulkReject()">
                    <i class="bi bi-x-circle me-1"></i>Reject Jobs
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.quick-actions-fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: all 0.3s;
    z-index: 999;
}

.quick-actions-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
}

.quick-actions-fab i {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.qa-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    border: 2px solid white;
}

.quick-actions-panel {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 400px;
    max-height: 600px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 998;
    display: flex;
    flex-direction: column;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.qa-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
}

.qa-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.qa-close:hover {
    background: rgba(255,255,255,0.3);
}

.qa-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
}

.qa-summary {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: #075985;
}

.qa-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.qa-tab {
    flex: 1;
    padding: 0.75rem 0.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 0.875rem;
    color: #6b7280;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    position: relative;
}

.qa-tab:hover {
    color: #667eea;
    background: #f9fafb;
}

.qa-tab.active {
    color: #667eea;
    border-bottom-color: #667eea;
    font-weight: 600;
}

.qa-tab i {
    font-size: 1.25rem;
}

.qa-tab-badge {
    position: absolute;
    top: 5px;
    right: 10px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 700;
}

.qa-section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.qa-list {
    max-height: 300px;
    overflow-y: auto;
}

.qa-list-item {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    transition: all 0.2s;
}

.qa-list-item:hover {
    border-color: #667eea;
    background: #f9fafb;
}

.qa-list-item.selected {
    border-color: #667eea;
    background: #ede9fe;
}

.qa-item-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.qa-item-title {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.qa-item-meta {
    font-size: 0.75rem;
    color: #6b7280;
}

.form-label-sm {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-check-sm {
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .quick-actions-panel {
        width: calc(100vw - 40px);
        right: 20px;
        bottom: 90px;
    }

    .quick-actions-fab {
        bottom: 20px;
        right: 20px;
    }
}
</style>

<script>
let selectedJobs = [];
let selectedKyc = [];
let pendingJobsData = [];
let pendingKycData = [];

// Initialize Quick Actions Widget
document.addEventListener('DOMContentLoaded', function() {
    initializeQuickActions();
    loadQuickActionsData();

    // Refresh data every 2 minutes
    setInterval(loadQuickActionsData, 120000);
});

function initializeQuickActions() {
    const fab = document.getElementById('qaFab');
    const panel = document.getElementById('qaPanel');
    const closeBtn = document.getElementById('qaClose');

    fab.addEventListener('click', () => {
        panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
    });

    closeBtn.addEventListener('click', () => {
        panel.style.display = 'none';
    });

    // Tab switching
    document.querySelectorAll('.qa-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;

            // Update active tab
            document.querySelectorAll('.qa-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Update active content
            document.querySelectorAll('.qa-tab-pane').forEach(pane => {
                pane.style.display = 'none';
            });
            document.getElementById(tabName + 'Tab').style.display = 'block';
        });
    });

    // Mass email form
    document.getElementById('massEmailForm').addEventListener('submit', handleMassEmail);
}

function loadQuickActionsData() {
    fetch('{{ route('admin.quick-actions.dashboard') }}')
        .then(res => res.json())
        .then(data => {
            updateQuickActionsSummary(data);
            loadPendingJobs();
            loadPendingKyc();
        });
}

function updateQuickActionsSummary(data) {
    const badge = document.getElementById('qaBadge');
    const summary = document.getElementById('qaSummary');
    const total = data.actions_available || 0;

    document.getElementById('jobsBadge').textContent = data.pending_jobs || 0;
    document.getElementById('kycBadge').textContent = data.pending_kyc || 0;

    if (total > 0) {
        badge.style.display = 'flex';
        badge.textContent = total;
        summary.innerHTML = `
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>${total} pending action${total !== 1 ? 's' : ''}</strong> require your attention
        `;
    } else {
        badge.style.display = 'none';
        summary.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>
            All caught up! No pending actions.
        `;
    }
}

function loadPendingJobs() {
    fetch('{{ route('admin.jobs.pending') }}?ajax=1')
        .then(res => res.json())
        .then(data => {
            pendingJobsData = data.data || [];
            renderPendingJobs();
        })
        .catch(() => {
            document.getElementById('pendingJobsList').innerHTML =
                '<div class="text-center text-muted py-3">Failed to load jobs</div>';
        });
}

function renderPendingJobs() {
    const container = document.getElementById('pendingJobsList');

    if (pendingJobsData.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-3">No pending jobs</div>';
        return;
    }

    container.innerHTML = pendingJobsData.slice(0, 10).map(job => `
        <div class="qa-list-item" id="job-${job.id}">
            <div class="d-flex align-items-start gap-2">
                <input type="checkbox" class="qa-item-checkbox" value="${job.id}"
                       onchange="toggleJobSelection(${job.id}, this.checked)">
                <div class="flex-grow-1">
                    <div class="qa-item-title">${job.title}</div>
                    <div class="qa-item-meta">
                        <i class="bi bi-building me-1"></i>${job.company_name || 'N/A'}
                        <span class="ms-2">
                            <i class="bi bi-geo-alt me-1"></i>${job.location || 'N/A'}
                        </span>
                    </div>
                    <div class="qa-item-meta mt-1">
                        Posted ${new Date(job.created_at).toLocaleDateString()}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function toggleJobSelection(jobId, selected) {
    const item = document.getElementById(`job-${jobId}`);

    if (selected) {
        selectedJobs.push(jobId);
        item.classList.add('selected');
    } else {
        selectedJobs = selectedJobs.filter(id => id !== jobId);
        item.classList.remove('selected');
    }

    updateJobActionButtons();
}

function updateJobActionButtons() {
    const approveBtn = document.getElementById('bulkApproveBtn');
    const rejectBtn = document.getElementById('bulkRejectBtn');
    const hasSelection = selectedJobs.length > 0;

    approveBtn.disabled = !hasSelection;
    rejectBtn.disabled = !hasSelection;
}

function bulkApproveJobs() {
    if (selectedJobs.length === 0) return;

    if (!confirm(`Approve ${selectedJobs.length} selected job(s)?`)) return;

    const approveBtn = document.getElementById('bulkApproveBtn');
    approveBtn.disabled = true;
    approveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Approving...';

    fetch('{{ route('admin.quick-actions.bulk-approve-jobs') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ job_ids: selectedJobs })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast(data.message, 'success');
            selectedJobs = [];
            loadQuickActionsData();
        } else {
            showAdminToast(data.message || 'Failed to approve jobs', 'error');
        }
    })
    .catch(err => {
        showAdminToast('An error occurred', 'error');
    })
    .finally(() => {
        approveBtn.disabled = false;
        approveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Approve Selected';
    });
}

function showBulkRejectModal() {
    if (selectedJobs.length === 0) return;
    new bootstrap.Modal(document.getElementById('bulkRejectModal')).show();
}

function confirmBulkReject() {
    const reason = document.getElementById('rejectionReason').value.trim();

    if (reason.length < 10) {
        alert('Please provide a rejection reason (at least 10 characters)');
        return;
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkRejectModal'));
    modal.hide();

    fetch('{{ route('admin.quick-actions.bulk-reject-jobs') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            job_ids: selectedJobs,
            rejection_reason: reason
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast(data.message, 'success');
            selectedJobs = [];
            document.getElementById('rejectionReason').value = '';
            loadQuickActionsData();
        } else {
            showAdminToast(data.message || 'Failed to reject jobs', 'error');
        }
    })
    .catch(err => {
        showAdminToast('An error occurred', 'error');
    });
}

function loadPendingKyc() {
    // Mock data for now - replace with actual endpoint
    fetch('{{ route('admin.users.index') }}?kyc_status=in_progress&ajax=1')
        .then(res => res.json())
        .then(data => {
            pendingKycData = data.data || [];
            renderPendingKyc();
        })
        .catch(() => {
            document.getElementById('pendingKycList').innerHTML =
                '<div class="text-center text-muted py-3">Failed to load KYC data</div>';
        });
}

function renderPendingKyc() {
    const container = document.getElementById('pendingKycList');

    if (pendingKycData.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-3">No pending verifications</div>';
        return;
    }

    container.innerHTML = pendingKycData.slice(0, 10).map(user => `
        <div class="qa-list-item" id="kyc-${user.id}">
            <div class="d-flex align-items-start gap-2">
                <input type="checkbox" class="qa-item-checkbox" value="${user.id}"
                       onchange="toggleKycSelection(${user.id}, this.checked)">
                <div class="flex-grow-1">
                    <div class="qa-item-title">${user.name}</div>
                    <div class="qa-item-meta">
                        <i class="bi bi-envelope me-1"></i>${user.email}
                    </div>
                    <div class="qa-item-meta mt-1">
                        ${user.role ? `<span class="badge bg-secondary">${user.role}</span>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function toggleKycSelection(userId, selected) {
    const item = document.getElementById(`kyc-${userId}`);

    if (selected) {
        selectedKyc.push(userId);
        item.classList.add('selected');
    } else {
        selectedKyc = selectedKyc.filter(id => id !== userId);
        item.classList.remove('selected');
    }

    document.getElementById('bulkVerifyBtn').disabled = selectedKyc.length === 0;
}

function bulkVerifyKyc() {
    if (selectedKyc.length === 0) return;

    if (!confirm(`Verify KYC for ${selectedKyc.length} selected user(s)?`)) return;

    const verifyBtn = document.getElementById('bulkVerifyBtn');
    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verifying...';

    fetch('{{ route('admin.quick-actions.bulk-verify-kyc') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ user_ids: selectedKyc })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast(data.message, 'success');
            selectedKyc = [];
            loadQuickActionsData();
        } else {
            showAdminToast(data.message || 'Failed to verify KYC', 'error');
        }
    })
    .catch(err => {
        showAdminToast('An error occurred', 'error');
    })
    .finally(() => {
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i>Verify Selected';
    });
}

function handleMassEmail(e) {
    e.preventDefault();

    const formData = {
        segment: document.getElementById('emailSegment').value,
        subject: document.getElementById('emailSubject').value,
        message: document.getElementById('emailMessage').value,
        send_email: document.getElementById('sendEmail').checked,
        send_notification: document.getElementById('sendNotification').checked
    };

    if (!confirm(`Send this message to all users in the "${formData.segment}" segment?`)) {
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';

    fetch('{{ route('admin.quick-actions.send-mass-email') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast(data.message, 'success');
            document.getElementById('massEmailForm').reset();
        } else {
            showAdminToast(data.message || 'Failed to send messages', 'error');
        }
    })
    .catch(err => {
        showAdminToast('An error occurred', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send me-1"></i>Send Message';
    });
}
</script>
