@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Overview</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                @include('front.account.sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Profile Header Card -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="profile-image-container me-3">
                                    <div class="position-relative">
                                        @php
                                            $user = Auth::user();
                                            $imagePath = $user->image ? 'profile_img/thumb/'.$user->image : 'assets/images/avatar7.png';
                                            $fullImagePath = public_path($imagePath);
                                            $timestamp = time();
                                            
                                            // Debug information
                                            $debugInfo = [
                                                'User ID' => $user->id,
                                                'Image name' => $user->image,
                                                'Full path' => $fullImagePath,
                                                'File exists' => file_exists($fullImagePath) ? 'Yes' : 'No',
                                                'Public URL' => asset($imagePath),
                                                'Thumb directory exists' => file_exists(public_path('profile_img/thumb')) ? 'Yes' : 'No'
                                            ];
                                        @endphp
                                        
                                        <!-- Debug info (always visible) -->
                                        <div class="small text-muted mb-2">
                                            @foreach($debugInfo as $key => $value)
                                                <strong>{{ $key }}:</strong> {{ $value }}<br>
                                            @endforeach
                                        </div>

                                        @if (Auth::user()->image != '' && file_exists(public_path('profile_img/thumb/'.Auth::user()->image)))
                                            <img src="{{ asset('profile_img/thumb/'.Auth::user()->image) }}?v={{ $timestamp }}" 
                                                alt="Profile" 
                                                class="profile-image"
                                                onerror="this.onerror=null; this.src='{{ asset('assets/images/avatar7.png') }}';">
                                        @else
                                            <img src="{{ asset('assets/images/avatar7.png') }}" alt="Profile" class="profile-image">
                                        @endif
                                        <!-- Debug info -->
                                        @if(config('app.debug'))
                                            <div style="display: none;">
                                                Image path: {{ $imagePath }}<br>
                                                Exists: {{ file_exists($fullImagePath) ? 'Yes' : 'No' }}<br>
                                                User image: {{ Auth::user()->image ?? 'none' }}
                                            </div>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0" data-bs-toggle="modal" data-bs-target="#updateProfilePicModal">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="fs-4 mb-1">{{ Auth::user()->name }}</h3>
                                    <p class="text-muted mb-2">{{ Auth::user()->designation ?? 'Job Seeker' }}</p>
                                    <div class="tags">
                                        @if(Auth::user()->role)
                                            <span class="badge bg-light text-dark">{{ ucfirst(Auth::user()->role) }}</span>
                                        @endif
                                        @if(Auth::user()->skills)
                                            @foreach(array_slice(Auth::user()->skills ?? [], 0, 3) as $skill)
                                                <span class="badge bg-light text-dark">{{ $skill }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('account.settings') }}" class="btn btn-primary">Edit Profile</a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Career Findings Column -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Career Findings</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <strong class="d-block">Location</strong>
                                        <span class="text-muted">{{ Auth::user()->location ?? 'Not specified' }}</span>
                                    </li>
                                    <li class="mb-3">
                                        <strong class="d-block">Phone Number</strong>
                                        <span class="text-muted">{{ Auth::user()->mobile ?? 'Not specified' }}</span>
                                    </li>
                                    <li class="mb-3">
                                        <strong class="d-block">Experience Time</strong>
                                        <span class="text-muted">{{ Auth::user()->experience_years ?? '0' }} years</span>
                                    </li>
                                    <li class="mb-3">
                                        <strong class="d-block">Email</strong>
                                        <span class="text-muted">{{ Auth::user()->email }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div class="card border-0 shadow mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Socials</h5>
                                <ul class="list-unstyled">
                                    @php
                                        $socials = Auth::user()->social_links ?? [];
                                    @endphp
                                    @if(!empty($socials))
                                        @foreach($socials as $platform => $link)
                                            <li class="mb-2">
                                                <i class="fab fa-{{ strtolower($platform) }} me-2"></i>
                                                <a href="{{ $link }}" target="_blank">{{ ucfirst($platform) }}</a>
                                            </li>
                                        @endforeach
                                    @else
                                        <li class="text-muted">No social links added</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- About Me and Education Column -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">About me</h5>
                                <p>{{ Auth::user()->bio ?? 'No bio information added yet.' }}</p>
                            </div>
                        </div>

                        <div class="card border-0 shadow mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Education</h5>
                                @if(!empty(Auth::user()->education) && is_array(Auth::user()->education))
                                    @foreach(Auth::user()->education as $edu)
                                        <div class="education-item mb-3">
                                            <h6 class="mb-1">{{ $edu }}</h6>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">No education details added</p>
                                @endif
                            </div>
                        </div>

                        <!-- Recent Job Applications -->
                        <div class="card border-0 shadow">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Job Applied Recently</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>JOBS</th>
                                                <th>STATUS</th>
                                                <th>DATE APPLIED</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(Auth::user()->jobApplications()->latest()->take(5)->get() as $application)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="company-logo me-2">
                                                                @if($application->job->employer->employerProfile->company_logo)
                                                                    <img src="{{ asset('storage/'.$application->job->employer->employerProfile->company_logo) }}" alt="Company Logo" width="40">
                                                                @else
                                                                    <div class="company-initial">{{ substr($application->job->employer->employerProfile->company_name ?? 'C', 0, 1) }}</div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $application->job->title }}</h6>
                                                                <small class="text-muted">{{ $application->job->employer->employerProfile->company_name ?? 'Company Name' }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $application->status == 'pending' ? 'warning' : ($application->status == 'accepted' ? 'success' : 'danger') }}">
                                                            {{ ucfirst($application->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $application->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-link" type="button" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="{{ route('jobDetail', $application->job->id) }}">View Job</a></li>
                                                                <li><a class="dropdown-item" href="#">View Application</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.profile-image-container {
    width: 100px;
    height: 100px;
    position: relative;
}

.profile-image-container .btn {
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 0.25rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.profile-image-container:hover .btn {
    opacity: 1;
}

.profile-image {
    width: 100%;
    height: 100%;
    border-radius: 15px;
    object-fit: cover;
}

.tags .badge {
    margin-right: 5px;
}

.company-initial {
    width: 40px;
    height: 40px;
    background-color: #e9ecef;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.education-item {
    padding-left: 15px;
    border-left: 3px solid #e9ecef;
}

.table > :not(caption) > * > * {
    padding: 1rem;
}

@media (max-width: 991.98px) {
    .col-lg-3 {
        margin-bottom: 2rem;
    }
}
</style>

<!-- Profile Picture Update Modal -->
<div class="modal fade" id="updateProfilePicModal" tabindex="-1" aria-labelledby="updateProfilePicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProfilePicModalLabel">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="profilePicForm">
                    @csrf
                    <div class="mb-3">
                        <label for="image" class="form-label">Choose Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF (Max size: 2MB)</div>
                    </div>
                    <div class="alert alert-danger d-none" id="imageError"></div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Upload Picture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('profilePicForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';
    submitBtn.disabled = true;
    
    // Hide any previous errors
    document.getElementById('imageError').classList.add('d-none');
    
    fetch('{{ route("account.updateProfileimg") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data); // Debug log
        if (data.status) {
            // Force page reload to ensure everything is updated
            window.location.reload();
            
            console.log('Updating image URL to:', newImageUrl); // Debug log
            
            // First verify the image is accessible
            fetch(newImageUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Image not accessible');
                    console.log('Image is accessible, updating src');
                    
                    profileImages.forEach(img => {
                        img.setAttribute('src', newImageUrl);
                        // Force reload by temporarily removing and re-adding the element
                        const parent = img.parentNode;
                        const next = img.nextSibling;
                        parent.removeChild(img);
                        parent.insertBefore(img, next);
            });
            
            // Close modal and show success message
            const modal = bootstrap.Modal.getInstance(document.getElementById('updateProfilePicModal'));
            modal.hide();
            toastr.success('Profile picture updated successfully');
        } else {
            // Show error
            const errorDiv = document.getElementById('imageError');
            errorDiv.classList.remove('d-none');
            errorDiv.textContent = data.errors.image ? data.errors.image[0] : 'Failed to upload image. Please try again.';
            
            // Reset button
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        // Show error
        const errorDiv = document.getElementById('imageError');
        errorDiv.classList.remove('d-none');
        errorDiv.textContent = 'An error occurred. Please try again.';
        
        // Reset button
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
});
</script>
@endpush
@endsection
