@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">AI Job Match</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('components.sidebar')
            </div>
            <div class="col-lg-9">
                <!-- Job Match Score Card -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="fs-4 mb-1">AI Job Match Analysis</h3>
                        <p class="mb-4 text-muted">See how well you match with jobs and get personalized recommendations</p>

                        <!-- Job Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Select a job to see your match score and get detailed skill gap analysis.
                                </div>
                            </div>
                        </div>

                        <form id="jobMatchForm" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label">Select Job</label>
                                <select class="form-select" name="job_id" required>
                                    <option value="">Choose a job...</option>
                                    @foreach(Auth::user()->savedJobs as $savedJob)
                                        <option value="{{ $savedJob->job->id }}">{{ $savedJob->job->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-percentage me-2"></i>Calculate Match Score
                            </button>
                        </form>

                        <!-- Match Results -->
                        <div id="matchResults" class="d-none">
                            <!-- Match Score -->
                            <div class="match-score-container text-center mb-4">
                                <div class="progress-circle mx-auto position-relative" style="width: 150px; height: 150px;">
                                    <div class="progress-circle-inner d-flex align-items-center justify-content-center">
                                        <div>
                                            <h2 class="match-percentage mb-0">0%</h2>
                                            <p class="mb-0">Match Score</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Skill Gap Analysis -->
                            <div class="skill-analysis mt-4">
                                <h4 class="mb-3">Skill Gap Analysis</h4>
                                <div class="border rounded p-3 bg-light">
                                    <div id="skillAnalysis"></div>
                                </div>
                            </div>

                            <!-- Missing Skills -->
                            <div class="missing-skills mt-4">
                                <h4 class="mb-3">Missing Skills</h4>
                                <div id="missingSkills" class="d-flex flex-wrap gap-2">
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

@section('styles')
<style>
.progress-circle {
    background: conic-gradient(var(--primary-color) 0%, #f0f0f0 0%);
    border-radius: 50%;
    transition: background 0.3s ease;
}

.progress-circle-inner {
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
    background: white;
    border-radius: 50%;
}

.match-percentage {
    color: var(--primary-color);
    font-size: 2rem;
    font-weight: bold;
}

.skill-badge {
    background-color: #e9ecef;
    color: #495057;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#jobMatchForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Calculating...');
        
        const jobId = form.find('select[name="job_id"]').val();
        
        $.ajax({
            type: 'POST',
            url: '{{ route("account.ai.job-match.analyze") }}',
            data: {
                job_id: jobId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update match score
                    const matchScore = response.match_score;
                    $('.progress-circle').css('background', `conic-gradient(var(--primary-color) ${matchScore}%, #f0f0f0 ${matchScore}%)`);
                    $('.match-percentage').text(matchScore + '%');
                    
                    // Update skill analysis
                    $('#skillAnalysis').html(response.skill_gap_analysis.replace(/\n/g, '<br>'));
                    
                    // Update missing skills
                    const missingSkillsHtml = response.missing_skills.map(skill => 
                        `<span class="skill-badge">${skill}</span>`
                    ).join('');
                    $('#missingSkills').html(missingSkillsHtml);
                    
                    // Show results
                    $('#matchResults').removeClass('d-none');
                }
            },
            error: function() {
                alert('An error occurred while calculating the match score.');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-percentage me-2"></i>Calculate Match Score');
            }
        });
    });
});
</script>
@endsection 