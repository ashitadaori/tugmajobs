@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">AI Resume Builder</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('components.sidebar')
            </div>
            <div class="col-lg-9">
                <!-- AI Resume Builder Card -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="fs-4 mb-1">AI Resume Builder</h3>
                        <p class="mb-4 text-muted">Let AI help you create and optimize your resume</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Our AI will analyze your profile data and create a professional resume. You can also optimize it for specific job postings.
                                </div>
                            </div>
                        </div>

                        <!-- Generate Resume Form -->
                        <form id="generateResumeForm" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label">Target Job (Optional)</label>
                                <select class="form-select" name="job_id">
                                    <option value="">Generate general resume</option>
                                    @foreach(Auth::user()->savedJobs as $savedJob)
                                        <option value="{{ $savedJob->job->id }}">{{ $savedJob->job->title }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select a job to optimize your resume for that position</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magic me-2"></i>Generate Resume
                            </button>
                        </form>

                        <!-- Resume Output -->
                        <div id="resumeOutput" class="d-none">
                            <h4 class="mb-3">Your Generated Resume</h4>
                            <div class="border rounded p-3 bg-light">
                                <pre id="generatedResume" class="mb-0" style="white-space: pre-wrap;"></pre>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary btn-sm" onclick="copyToClipboard()">
                                    <i class="fas fa-copy me-2"></i>Copy to Clipboard
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="downloadResume()">
                                    <i class="fas fa-download me-2"></i>Download as PDF
                                </button>
                            </div>
                        </div>

                        <!-- Resume Analysis Form -->
                        <div class="mt-5">
                            <h4 class="mb-3">Analyze Existing Resume</h4>
                            <form id="analyzeResumeForm">
                                <div class="mb-3">
                                    <label class="form-label">Paste your existing resume text</label>
                                    <textarea class="form-control" name="resume_text" rows="6" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Analyze Resume
                                </button>
                            </form>
                            
                            <!-- Analysis Output -->
                            <div id="analysisOutput" class="mt-4 d-none">
                                <h5>Analysis Results</h5>
                                <div class="border rounded p-3 bg-light">
                                    <div id="analysisResult"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Generate Resume
    $('#generateResumeForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
        
        $.ajax({
            type: 'POST',
            url: '{{ route("account.ai.resume-builder.generate") }}',
            data: {
                job_id: form.find('select[name="job_id"]').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#generatedResume').text(response.resume);
                    $('#resumeOutput').removeClass('d-none');
                } else {
                    alert(response.message || 'Failed to generate resume. Please try again.');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while generating the resume.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-magic me-2"></i>Generate Resume');
            }
        });
    });

    // Analyze Resume
    $('#analyzeResumeForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        const resumeText = form.find('textarea[name="resume_text"]').val().trim();
        if (!resumeText) {
            alert('Please paste your resume text before analyzing.');
            return;
        }
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Analyzing...');
        
        $.ajax({
            type: 'POST',
            url: '{{ route("account.ai.resume-builder.analyze") }}',
            data: {
                resume_text: resumeText,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#analysisResult').html(response.analysis.replace(/\n/g, '<br>'));
                    $('#analysisOutput').removeClass('d-none');
                } else {
                    alert(response.message || 'Failed to analyze resume. Please try again.');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while analyzing the resume.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-search me-2"></i>Analyze Resume');
            }
        });
    });
});

function copyToClipboard() {
    const text = $('#generatedResume').text();
    if (!text.trim()) {
        alert('No resume content to copy.');
        return;
    }
    navigator.clipboard.writeText(text).then(function() {
        alert('Resume copied to clipboard!');
    }).catch(function() {
        alert('Failed to copy resume. Please try selecting and copying manually.');
    });
}

function downloadResume() {
    const text = $('#generatedResume').text();
    if (!text.trim()) {
        alert('No resume content to download.');
        return;
    }
    const element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', 'my-resume.txt');
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}
</script>
@endsection 