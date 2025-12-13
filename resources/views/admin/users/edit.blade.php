@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Users</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('admin.sidebar')
            </div>
            <div class="col-lg-9">
                @include('front.message')
                <div class="card border-0 shadow mb-4">
                    <div class="card-body card-form">
                        <form action="" method="post" id="editUser" name="editUser">
                            @csrf
                            <div class="card-body  p-4">
                                <h3 class="fs-4 mb-1">User / Edit</h3>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Name*</label>
                                    <input type="text" value="{{ $user->name }}" name="name" id="name" placeholder="Enter Name" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Email*</label>
                                    <input type="text" value="{{ $user->email }}" name="email" id="email" placeholder="Enter Email" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Designation</label>
                                    <input type="text" value="{{ $user->designation }}" name="designation" id="designation" placeholder="Designation" class="form-control">
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Mobile</label>
                                    <input type="text" value="{{ $user->mobile }}" name="mobile" id="mobile" placeholder="Mobile" class="form-control">
                                </div>

                                {{-- Job Seeker Resume Section --}}
                                @if($user->role === 'jobseeker' && $user->jobSeekerProfile && $user->jobSeekerProfile->resume_file)
                                    <div class="mb-4">
                                        <label class="mb-2">Resume</label>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                                        <strong>{{ $user->jobSeekerProfile->resume_file }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            Uploaded: {{ $user->jobSeekerProfile->updated_at->format('M d, Y h:i A') }}
                                                        </small>
                                                    </div>
                                                    <a href="{{ asset('storage/resumes/' . $user->jobSeekerProfile->resume_file) }}" 
                                                       class="btn btn-primary" target="_blank">
                                                        <i class="fas fa-download me-2"></i>Download Resume
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($user->role === 'jobseeker')
                                    <div class="mb-4">
                                        <label class="mb-2">Resume</label>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            No resume uploaded yet
                                        </div>
                                    </div>
                                @endif

                                {{-- Employer Company Info Section --}}
                                @if($user->role === 'employer' && $user->employerProfile)
                                    <div class="mb-4">
                                        <label class="mb-2">Company Information</label>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Company Name:</strong><br>{{ $user->employerProfile->company_name ?? 'N/A' }}</p>
                                                        <p class="mb-2"><strong>Website:</strong><br>{{ $user->employerProfile->website ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Location:</strong><br>{{ $user->employerProfile->location ?? 'N/A' }}</p>
                                                        <p class="mb-2"><strong>Company Size:</strong><br>{{ $user->employerProfile->company_size ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                                @if($user->employerProfile->company_logo)
                                                    <div class="mt-3">
                                                        <strong>Company Logo:</strong><br>
                                                        <img src="{{ $user->employerProfile->logo_url }}" alt="Company Logo" style="max-width: 200px; max-height: 100px;" class="mt-2">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer  p-4">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('customJs')
    <script type="text/javascript">


$("#editUser").submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: "put",
            url: "{{ route('admin.user.update',$user->id) }}",
            data: $("#editUser").serializeArray(),
            dataType: "json",
            success: function (response) {
                if(response.status == true){

                    $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    $("#email").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    window.location.href="{{ route('admin.users') }}";

                }else{

                    var errors = response.errors;
                    // For name
                    if(errors.name){
                        $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
                    }else{
                        $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }

                    // For email
                    if(errors.email){
                        $("#email").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.email);
                    }else{
                        $("#email").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                }
            }
        });

    });

    </script>
@endsection
