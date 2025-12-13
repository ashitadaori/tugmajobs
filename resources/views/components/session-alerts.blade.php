{{-- Session Alert Messages Component --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-left: 4px solid #28a745; animation: slideDown 0.3s ease-out;">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-3" style="font-size: 1.5rem; color: #28a745;"></i>
            <div class="flex-grow-1">
                <strong>Success!</strong> {{ session('success') }}
                @if(session('job_id'))
                    <br><small class="text-muted">Job ID: {{ session('job_id') }} | Status: {{ session('job_status', 'Created') }}</small>
                @endif
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-left: 4px solid #dc3545; animation: slideDown 0.3s ease-out;">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem; color: #dc3545;"></i>
            <div class="flex-grow-1">
                <strong>Error!</strong> {{ session('error') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-left: 4px solid #ffc107; animation: slideDown 0.3s ease-out;">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-3" style="font-size: 1.5rem; color: #ffc107;"></i>
            <div class="flex-grow-1">
                <strong>Warning!</strong> {{ session('warning') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-left: 4px solid #0dcaf0; animation: slideDown 0.3s ease-out;">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-3" style="font-size: 1.5rem; color: #0dcaf0;"></i>
            <div class="flex-grow-1">
                <strong>Info!</strong> {{ session('info') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<style>
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
