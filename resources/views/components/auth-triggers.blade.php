<!-- Auth Trigger Buttons -->
<div class="auth-triggers">
    <!-- Login Button -->
    <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToLogin()">
        <i class="fas fa-sign-in-alt me-1"></i>
        Sign In
    </button>
    
    <!-- Register Button -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToRegister()">
        <i class="fas fa-user-plus me-1"></i>
        Sign Up
    </button>
</div>

<!-- Alternative: Text Links -->
<div class="auth-links d-none">
    <a href="#" class="text-decoration-none me-3" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToLogin()">
        Sign In
    </a>
    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToRegister()">
        Sign Up
    </a>
</div>

<!-- Alternative: Single CTA Button -->
<div class="auth-cta d-none">
    <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchToRegister()">
        <i class="fas fa-user-plus me-2"></i>
        Get Started Free
    </button>
</div>