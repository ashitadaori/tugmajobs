@extends('layouts.employer')

@section('page_title', 'Debug Profile Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Debug Profile Update Form</h5>
                    <small class="text-muted">Use this form to debug profile update issues</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Current Profile Data Debug -->
                        <div class="col-md-6">
                            <h6 class="text-primary">Current Profile Data</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <strong>User ID:</strong> {{ Auth::id() }}<br>
                                <strong>User Name:</strong> {{ Auth::user()->name }}<br>
                                <strong>User Email:</strong> {{ Auth::user()->email }}<br>
                                
                                @if(Auth::user()->employerProfile)
                                    <hr>
                                    <strong>Company Name:</strong> {{ Auth::user()->employerProfile->company_name ?? 'N/A' }}<br>
                                    <strong>Company Logo Path:</strong> {{ Auth::user()->employerProfile->company_logo ?? 'N/A' }}<br>
                                    <strong>Company Logo URL:</strong> 
                                    @if(Auth::user()->employerProfile->company_logo)
                                        {{ Storage::url(Auth::user()->employerProfile->company_logo) }}
                                        <br><strong>File Exists:</strong> {{ Storage::exists(Auth::user()->employerProfile->company_logo) ? 'Yes' : 'No' }}
                                    @else
                                        N/A
                                    @endif
                                    <br>
                                    <strong>Website:</strong> {{ Auth::user()->employerProfile->website ?? 'N/A' }}<br>
                                    <strong>Description:</strong> {{ Str::limit(Auth::user()->employerProfile->company_description ?? 'N/A', 100) }}<br>
                                    <strong>Updated At:</strong> {{ Auth::user()->employerProfile->updated_at ?? 'N/A' }}<br>
                                @else
                                    <hr>
                                    <span class="text-warning">No employer profile found</span>
                                @endif
                            </div>

                            <!-- Current Sidebar Image Preview -->
                            <h6 class="text-primary">Current Sidebar Image</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                @if(Auth::user()->employerProfile && Auth::user()->employerProfile->company_logo)
                                    <img src="{{ Storage::url(Auth::user()->employerProfile->company_logo) }}" 
                                         alt="Current Profile" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                    <br><small class="text-muted">Image URL: {{ Storage::url(Auth::user()->employerProfile->company_logo) }}</small>
                                @else
                                    <div class="bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <br><small class="text-muted">No image - showing default</small>
                                @endif
                            </div>
                        </div>

                        <!-- Debug Form -->
                        <div class="col-md-6">
                            <h6 class="text-primary">Profile Update Form</h6>
                            <form action="{{ route('employer.profile.update') }}" method="POST" enctype="multipart/form-data" id="debugProfileForm">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name', Auth::user()->name) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email', Auth::user()->email) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" 
                                           value="{{ old('company_name', Auth::user()->employerProfile->company_name ?? '') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="company_logo" class="form-label">Company Logo</label>
                                    <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                                    <div class="form-text">Choose a new logo to upload</div>
                                </div>

                                <div class="mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="{{ old('website', Auth::user()->employerProfile->website ?? '') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="company_description" class="form-label">Company Description</label>
                                    <textarea class="form-control" id="company_description" name="company_description" rows="4">{{ old('company_description', Auth::user()->employerProfile->company_description ?? '') }}</textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Update Profile (Debug)</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debug Output Area -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Debug Information</h6>
                </div>
                <div class="card-body">
                    <div id="debugOutput">
                        <p class="text-muted">Submit the form to see debug information...</p>
                    </div>
                </div>
            </div>

            <!-- Request/Response Debug -->
            @if(session('debug_info'))
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Last Update Debug Info</h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3">{{ print_r(session('debug_info'), true) }}</pre>
                    </div>
                </div>
            @endif

            <!-- Browser Storage Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Browser Cache Debug</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-warning mb-3" onclick="clearBrowserCache()">Clear Browser Cache</button>
                    <button type="button" class="btn btn-info mb-3" onclick="forceRefresh()">Force Refresh Page</button>
                    <div id="cacheInfo" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add form submission debugging
    document.getElementById('debugProfileForm').addEventListener('submit', function(e) {
        const debugOutput = document.getElementById('debugOutput');
        debugOutput.innerHTML = '<div class="alert alert-info">Form submitted! Check browser Network tab for request details...</div>';
        
        // Log form data for debugging
        const formData = new FormData(this);
        console.log('Form submission debug:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }
        
        // Store current sidebar image for comparison
        const currentSidebarImage = document.querySelector('.sidebar .employer-avatar');
        if (currentSidebarImage && currentSidebarImage.tagName === 'IMG') {
            const currentSrc = currentSidebarImage.src;
            sessionStorage.setItem('debug_sidebar_image_before', currentSrc);
            console.log('Sidebar image before update:', currentSrc);
        }
        
        // Add timestamp to track when form was submitted
        const timestamp = new Date().toISOString();
        debugOutput.innerHTML += `<div class="alert alert-secondary">Submitted at: ${timestamp}</div>`;
    });
    
    // Monitor sidebar image changes
    monitorSidebarImage();

    // Show cache information
    showCacheInfo();
});

function clearBrowserCache() {
    // Clear various browser caches
    if ('caches' in window) {
        caches.keys().then(function(names) {
            names.forEach(function(name) {
                caches.delete(name);
            });
        });
    }
    
    // Clear local storage
    localStorage.clear();
    sessionStorage.clear();
    
    alert('Browser cache cleared! Refreshing page...');
    location.reload(true);
}

function forceRefresh() {
    // Force refresh with cache bypass
    location.reload(true);
}

function showCacheInfo() {
    const cacheInfoDiv = document.getElementById('cacheInfo');
    let info = '<h6>Cache Information:</h6>';
    info += `<p><strong>Page loaded at:</strong> ${new Date().toISOString()}</p>`;
    info += `<p><strong>User Agent:</strong> ${navigator.userAgent}</p>`;
    info += `<p><strong>Local Storage items:</strong> ${localStorage.length}</p>`;
    info += `<p><strong>Session Storage items:</strong> ${sessionStorage.length}</p>`;
    
    cacheInfoDiv.innerHTML = info;
}

function monitorSidebarImage() {
    const sidebarImage = document.querySelector('.sidebar .employer-avatar');
    if (!sidebarImage) {
        console.log('No sidebar image found to monitor');
        return;
    }
    
    let lastImageSrc = '';
    if (sidebarImage.tagName === 'IMG') {
        lastImageSrc = sidebarImage.src;
    }
    
    // Check for image changes every 2 seconds
    setInterval(function() {
        const currentImage = document.querySelector('.sidebar .employer-avatar');
        if (currentImage && currentImage.tagName === 'IMG') {
            const currentSrc = currentImage.src;
            if (currentSrc !== lastImageSrc) {
                console.log('Sidebar image changed!');
                console.log('Old src:', lastImageSrc);
                console.log('New src:', currentSrc);
                
                const debugOutput = document.getElementById('debugOutput');
                if (debugOutput) {
                    debugOutput.innerHTML += `<div class="alert alert-success">Sidebar image updated! New URL: ${currentSrc}</div>`;
                }
                
                lastImageSrc = currentSrc;
            }
        }
    }, 2000);
    
    console.log('Monitoring sidebar image changes. Current src:', lastImageSrc);
}

// Add page visibility API to detect when user comes back to tab
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Page is now visible - user came back to this tab
        console.log('Page became visible - checking for updates...');
        
        // Check if sidebar image changed while tab was hidden
        const beforeSrc = sessionStorage.getItem('debug_sidebar_image_before');
        const currentImage = document.querySelector('.sidebar .employer-avatar');
        if (currentImage && currentImage.tagName === 'IMG' && beforeSrc) {
            const currentSrc = currentImage.src;
            if (currentSrc !== beforeSrc) {
                console.log('Sidebar image changed while tab was not visible!');
                const debugOutput = document.getElementById('debugOutput');
                if (debugOutput) {
                    debugOutput.innerHTML += '<div class="alert alert-warning">Sidebar image updated while tab was hidden!</div>';
                }
            }
        }
        
        // You could add logic here to refresh profile data
        // For now, just log it for debugging
        const debugOutput = document.getElementById('debugOutput');
        if (debugOutput) {
            debugOutput.innerHTML += '<div class="alert alert-info">Page became visible - profile data should refresh</div>';
        }
    }
});
</script>
@endsection
