@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col">
            <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('account.myProfile') }}">Overview</a></li>
                    <li class="breadcrumb-item active">Profile Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            @include('front.message')

            <!-- Avatar Upload Section -->
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-4">
                                <div class="profile-image">
                                    @if (Auth::user()->image != '')
                                        <img src="{{ asset('profile_img/thumb/'.Auth::user()->image) }}" alt="Profile" class="avatar-image" id="previewImage">
                                    @else
                                        <img src="{{ asset('assets/images/avatar7.png') }}" alt="Profile" class="avatar-image" id="previewImage">
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-2">Upload a new Avatar</h5>
                                    <form id="profileImageForm" action="{{ route('account.updateProfileimg') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="d-flex align-items-center gap-3">
                                            <label class="btn btn-light btn-sm px-4">
                                                Choose file
                                                <input type="file" id="avatarInput" name="image" class="d-none" accept="image/*">
                                            </label>
                                            <span class="text-muted small" id="fileNameDisplay">No file chosen</span>
                                            <button type="submit" class="btn btn-success btn-sm px-4" id="uploadImageBtn" style="display: none;">
                                                Upload Image
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Section -->
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Information</h5>
                        <form id="informationForm" method="POST" action="{{ route('account.updateProfile') }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', Auth::user()->name) }}" required>
                                    <span class="text-danger error-message" id="name_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', Auth::user()->email) }}" required>
                                    <span class="text-danger error-message" id="email_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile', Auth::user()->mobile) }}">
                                    <span class="text-danger error-message" id="mobile_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" name="job_title" class="form-control" value="{{ old('job_title', Auth::user()->job_title) }}">
                                    <span class="text-danger error-message" id="job_title_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" value="{{ old('location', Auth::user()->location) }}">
                                    <span class="text-danger error-message" id="location_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Salary</label>
                                    <input type="number" name="salary" class="form-control" value="{{ old('salary', Auth::user()->salary) }}">
                                    <span class="text-danger error-message" id="salary_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Salary Type</label>
                                    <select name="salary_type" class="form-control">
                                        <option value="">Select Type</option>
                                        @foreach(['Month', 'Year', 'Week', 'Hour'] as $type)
                                            <option value="{{ $type }}" {{ old('salary_type', Auth::user()->salary_type) == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-message" id="salary_type_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Qualification</label>
                                    <input type="text" name="qualification" class="form-control" value="{{ old('qualification', Auth::user()->qualification) }}">
                                    <span class="text-danger error-message" id="qualification_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Language</label>
                                    <input type="text" name="language" class="form-control" value="{{ old('language', Auth::user()->language) }}">
                                    <span class="text-danger error-message" id="language_error"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Categories</label>
                                    <input type="text" name="categories" class="form-control" value="{{ old('categories', Auth::user()->categories) }}">
                                    <span class="text-danger error-message" id="categories_error"></span>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Bio</label>
                                    <textarea name="bio" class="form-control" rows="4">{{ old('bio', Auth::user()->bio) }}</textarea>
                                    <span class="text-danger error-message" id="bio_error"></span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-success px-4" id="saveProfileBtn">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- About Company Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">About Company</h5>
                    <div class="editor-toolbar border rounded-top p-2 bg-light">
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-undo"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-redo"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-bold"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-italic"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-underline"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-strikethrough"></i></button>
                        <button type="button" class="btn btn-sm btn-light">H1</button>
                        <button type="button" class="btn btn-sm btn-light">H2</button>
                        <button type="button" class="btn btn-sm btn-light">H3</button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-list"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-list-ol"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-align-left"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-image"></i></button>
                        <button type="button" class="btn btn-sm btn-light"><i class="fas fa-link"></i></button>
                    </div>
                    <div class="border rounded-bottom p-3">
                        <textarea class="form-control border-0" rows="6" placeholder="Write about your company...">{{ Auth::user()->bio }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Social Network Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Social Network</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="fab fa-facebook text-primary"></i>
                                </span>
                                <input type="url" class="form-control" placeholder="Facebook URL" value="{{ Auth::user()->social_links['facebook'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="fab fa-linkedin text-primary"></i>
                                </span>
                                <input type="url" class="form-control" placeholder="LinkedIn URL" value="{{ Auth::user()->social_links['linkedin'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="fab fa-twitter text-info"></i>
                                </span>
                                <input type="url" class="form-control" placeholder="Twitter URL" value="{{ Auth::user()->social_links['twitter'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="fab fa-pinterest text-danger"></i>
                                </span>
                                <input type="url" class="form-control" placeholder="Pinterest URL" value="{{ Auth::user()->social_links['pinterest'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title">Change Password</h5>
                    <p class="text-muted">Ensure your account is using a long, random password to stay secure</p>
                    
                    <form id="passwordForm" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label">Current Password*</label>
                            <input type="password" class="form-control" placeholder="Enter current password">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password*</label>
                                <input type="password" class="form-control" placeholder="Enter new password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password*</label>
                                <input type="password" class="form-control" placeholder="Confirm new password">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock me-2"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title">Delete Account</h5>
                    <p class="text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                    
                    <div class="mt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="deleteConfirm">
                            <label class="form-check-label" for="deleteConfirm">
                                I understand that this action cannot be undone
                            </label>
                        </div>
                        <button type="button" class="btn btn-danger" id="deleteAccountBtn" disabled>
                            <i class="fas fa-trash-alt me-2"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
}

.editor-toolbar .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    margin-right: 0.25rem;
}

.editor-toolbar .btn:hover {
    background-color: #e9ecef;
}

.input-group-text {
    min-width: 45px;
    justify-content: center;
}

.form-control:focus {
    box-shadow: none;
    border-color: #dee2e6;
}

.btn-success {
    background-color: #82b440;
    border-color: #82b440;
}

.btn-success:hover {
    background-color: #6f9a37;
    border-color: #6f9a37;
}
</style>

@section('customJs')
<script>
$(document).ready(function() {
    // Clear error messages when user starts typing
    $('input, select, textarea').on('input', function() {
        $(this).siblings('.error-message').text('');
    });

    // Handle information form submission
    $('#informationForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous error messages
        $('.error-message').text('');
        
        // Disable submit button to prevent double submission
        $('#saveProfileBtn').prop('disabled', true);
        
        // Get form data
        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.status) {
                    // Redirect to profile page
                    window.location.href = '{{ route("account.myProfile") }}';
                } else {
                    if(response.errors) {
                        $.each(response.errors, function(key, value) {
                            $('#' + key + '_error').text(value[0]);
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                if (xhr.status === 422) { // Validation error
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('#' + key + '_error').text(value[0]);
                    });
                } else {
                    alert('Error updating profile. Please try again.');
                }
            },
            complete: function() {
                // Re-enable submit button
                $('#saveProfileBtn').prop('disabled', false);
            }
        });
    });

    // Handle profile image upload
    $('#profileImageForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        // Disable upload button
        $('#uploadImageBtn').prop('disabled', true);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.status) {
                    alert('Profile picture updated successfully!');
                    window.location.reload();
                } else {
                    if(response.errors) {
                        $.each(response.errors, function(key, value) {
                            $('#' + key + '_error').text(value[0]);
                        });
                    } else {
                        alert('Error updating profile picture. Please try again.');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('Error updating profile picture. Please try again.');
            },
            complete: function() {
                // Re-enable upload button
                $('#uploadImageBtn').prop('disabled', false);
            }
        });
    });

    // Handle file input change
    $('#avatarInput').change(function() {
        var fileName = $(this).val().split('\\').pop();
        $('#fileNameDisplay').text(fileName || 'No file chosen');
        $('#uploadImageBtn').show();
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImage').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Handle delete account checkbox
    $('#deleteConfirm').change(function() {
        $('#deleteAccountBtn').prop('disabled', !$(this).is(':checked'));
    });
});
</script>
@endsection

@endsection 