@extends('layouts.admin')

@section('page_title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Profile</h5>
                    <div class="small text-muted">Update your personal information</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Profile Image -->
                            <div class="col-md-3 text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ $user->profile_image ?? asset('images/default-profile.svg') }}"
                                         alt="Profile" class="rounded-circle img-thumbnail" id="profileImagePreview"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                    <label for="image" class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle"
                                           style="width: 40px; height: 40px; padding: 0; line-height: 40px; cursor: pointer;">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                </div>
                                <input type="file" id="image" name="image" class="d-none" accept="image/*">
                                @error('image')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                                <div class="small text-muted mt-2">Click the camera icon to change profile picture</div>
                            </div>

                            <!-- Profile Information -->
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Account Created</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $user->created_at->format('F d, Y') }}" disabled>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check me-2"></i>Save Changes
                                    </button>
                                    <a href="{{ route('admin.profile.security') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-shield-alt me-2"></i>Security Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('image').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImagePreview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
@endpush
@endsection 