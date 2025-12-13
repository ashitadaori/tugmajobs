@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Post a Job</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('components.sidebar')
            </div>
            <div class="col-lg-9">
                <form action="" method="post" name="createJobForm" id="createJobForm">
                    @csrf
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body card-form p-4">
                            <h3 class="fs-4 mb-1">Job Details</h3>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="" class="mb-2">Title<span class="req">*</span></label>
                                    <input type="text" placeholder="Job Title" id="title" name="title" class="form-control">
                                    <p></p>
                                </div>
                                <div class="col-md-6  mb-4">
                                    <label for="" class="mb-2">Category<span class="req">*</span></label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="">Select a Category</option>
                                        @if ($categories->isNotEmpty())
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="" class="mb-2">Job Type<span class="req">*</span></label>
                                    <select name="jobType" id="jobType" class="form-select">
                                        <option value="">Select Job Type</option>
                                        @if($job_types->isNotEmpty())
                                        @foreach ($job_types as $job_type)
                                            <option value="{{ $job_type->id }}">{{ $job_type->name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                                <div class="col-md-6  mb-4">
                                    <label for="" class="mb-2">Vacancy<span class="req">*</span></label>
                                    <input type="number" min="1" placeholder="Vacancy" id="vacancy" name="vacancy" class="form-control">
                                    <p></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-4 col-md-6">
                                    <label for="" class="mb-2">Salary</label>
                                    <input type="text" placeholder="Salary" id="salary" name="salary" class="form-control">
                                </div>

                                <div class="mb-4 col-md-6">
                                    <label for="location" class="mb-2">Location<span class="req">*</span></label>
                                    <select name="location" id="location" class="form-select">
                                        <option value="">Select Location in Sta. Cruz, Davao del Sur</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location['name'] }}" 
                                                data-lat="{{ $location['lat'] }}"
                                                data-lng="{{ $location['lng'] }}"
                                                data-address="{{ $location['name'] }}, Sta. Cruz, Davao del Sur">
                                                {{ $location['name'] }}, Sta. Cruz
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">
                                    <input type="hidden" name="location_address" id="location_address">
                                    <div class="invalid-feedback" id="location-error"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="" class="mb-2">Description<span class="req">*</span></label>
                                <textarea class="textarea" name="description" id="description" cols="5" rows="5" placeholder="Description"></textarea>
                                <p></p>
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Benefits</label>
                                <textarea class="textarea" name="benefits" id="benefits" cols="5" rows="5" placeholder="Benefits"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Responsibility</label>
                                <textarea class="textarea" name="responsibility" id="responsibility" cols="5" rows="5" placeholder="Responsibility"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Qualifications</label>
                                <textarea class="textarea" name="qualifications" id="qualifications" cols="5" rows="5" placeholder="Qualifications"></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="" class="mb-2">Experience<span class="req">*</span></label>
                                <select name="experience" id="experience" class="form-control">
                                    <option value="">Select Experience</option>
                                    <option value="1">1 Years</option>
                                    <option value="2">2 Years</option>
                                    <option value="3">3 Years</option>
                                    <option value="4">4 Years</option>
                                    <option value="5">5 Years</option>
                                    <option value="6">6 Years</option>
                                    <option value="7">7 Years</option>
                                    <option value="8">8 Years</option>
                                    <option value="9">9 Years</option>
                                    <option value="10">10 Years</option>
                                    <option value="10_plus">10+ Years</option>
                                </select>
                                <p></p>
                            </div>

                            <div class="mb-4">
                                <label for="" class="mb-2">Keywords</label>
                                <input type="text" placeholder="keywords" id="keywords" name="keywords" class="form-control">
                            </div>

                            <h3 class="fs-4 mb-1 mt-5 border-top pt-5">Company Details</h3>

                            <div class="row">
                                <div class="mb-4 col-md-6">
                                    <label for="" class="mb-2">Name<span class="req">*</span></label>
                                    <input type="text" placeholder="Company Name" id="company_name" name="company_name" class="form-control">
                                    <p></p>
                                </div>

                                <div class="mb-4 col-md-6">
                                    <label for="" class="mb-2">Location</label>
                                    <input type="text" placeholder="Location" id="company_location" name="company_location" class="form-control">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="" class="mb-2">Website</label>
                                <input type="text" placeholder="Website" id="company_website" name="company_website" class="form-control">
                            </div>
                        </div>
                        <div class="card-footer  p-4">
                            <button type="submit" class="btn btn-primary">Save Job</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</section>

@endsection

@section('customJs')
<script type="text/javascript">
    // Handle location selection
    $('#location').change(function() {
        var selectedOption = $(this).find(':selected');
        $('#latitude').val(selectedOption.data('lat'));
        $('#longitude').val(selectedOption.data('lng'));
        $('#location_address').val(selectedOption.data('address'));
        
        // Clear error if a location is selected
        if ($(this).val()) {
            $('#location-error').hide();
        }
    });

    // Form validation
    $("#createJobForm").submit(function(e) {
        e.preventDefault();
        
        // Validate location
        if (!$('#location').val()) {
            $('#location-error').text('Please select a location').show();
            return false;
        }
        
        $("button[type='submit']").prop('disabled', true);

        $.ajax({
            type: "POST",
            url: "{{ route('account.storeJob') }}",
            data: $(this).serializeArray(),
            dataType: "json",
            success: function(response) {
                $("button[type='submit']").prop('disabled', false);
                
                if (response.status) {
                    // Use window.location.href to perform the actual redirect
                    window.location.href = "{{ route('account.employer.dashboard') }}";
                } else {
                    if (response.errors) {
                        $.each(response.errors, function(field, messages) {
                            $('#' + field + '-error').text(messages[0]).show();
                        });
                    } else {
                        alert(response.message || 'An error occurred. Please try again.');
                    }
                }
            },
            error: function(xhr, status, error) {
                $("button[type='submit']").prop('disabled', false);
                alert('An error occurred. Please try again.');
            }
        });
    });
</script>
@endsection
