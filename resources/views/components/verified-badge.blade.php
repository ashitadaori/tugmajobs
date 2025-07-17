@props(['user', 'size' => 'sm', 'showText' => false])

@php
    $user = $user ?? Auth::user();
    $sizeClasses = [
        'xs' => 'fs-6',
        'sm' => 'fs-5', 
        'md' => 'fs-4',
        'lg' => 'fs-3'
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

@if($user && $user->isKycVerified())
    <span class="verified-badge d-inline-flex align-items-center" 
          title="Verified Profile" 
          data-bs-toggle="tooltip">
        <i class="fas fa-check-circle text-success {{ $sizeClass }} me-1"></i>
        @if($showText)
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="fas fa-check-circle me-1"></i>Verified
            </span>
        @endif
    </span>
@endif

<style>
.verified-badge {
    animation: verifiedPulse 2s infinite;
}

@keyframes verifiedPulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.verified-badge:hover {
    animation: none;
    opacity: 1;
}
</style>