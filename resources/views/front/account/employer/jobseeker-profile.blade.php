@extends('layouts.employer')

@section('page_title', $jobseeker->name . ' - Profile')

@section('content')
<div class="container-fluid" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    
    <!-- Back Button -->
    <div class="mb-3">
        <button onclick="window.history.back()" style="background: white; border: 1px solid #ddd; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Applications</span>
        </button>
    </div>

    <!-- Profile Header Card with Gradient -->
    <div style="background: #06923E; border-radius: 16px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3); overflow: hidden; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; padding: 2.5rem; position: relative;">
            <!-- Decorative circles -->
            <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            
            <img src="{{ $jobseeker->image ? asset('storage/' . $jobseeker->image) : asset('images/default-avatar.png') }}" 
                 alt="{{ $jobseeker->name }}" 
                 style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-right: 2rem; border: 4px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.2); position: relative; z-index: 1;">
            <div style="flex: 1; position: relative; z-index: 1;">
                <h2 style="margin: 0 0 0.5rem 0; font-size: 2rem; font-weight: 700; color: white !important;">{{ $jobseeker->name }}</h2>
                @if($jobseeker->jobSeekerProfile && $jobseeker->jobSeekerProfile->designation)
                    <p style="margin: 0 0 1.25rem 0; color: white !important; font-size: 1.2rem; font-weight: 500; opacity: 0.95;">{{ $jobseeker->jobSeekerProfile->designation }}</p>
                @endif
                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 0.5rem; color: white !important; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 20px; backdrop-filter: blur(10px);">
                        <i class="bi bi-envelope-fill" style="color: white !important;"></i> {{ $jobseeker->email }}
                    </span>
                    @if($jobseeker->mobile)
                        <span style="display: flex; align-items: center; gap: 0.5rem; color: white !important; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 20px; backdrop-filter: blur(10px);">
                            <i class="bi bi-telephone-fill" style="color: white !important;"></i> {{ $jobseeker->mobile }}
                        </span>
                    @endif
                    @if($jobseeker->jobSeekerProfile && $jobseeker->jobSeekerProfile->location)
                        <span style="display: flex; align-items: center; gap: 0.5rem; color: white !important; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 20px; backdrop-filter: blur(10px);">
                            <i class="bi bi-geo-alt-fill" style="color: white !important;"></i> {{ $jobseeker->jobSeekerProfile->location }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            
            <!-- Applications Section -->
            @if($applications->count() > 0)
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 1.5rem;">
                    <h5 style="margin: 0; display: flex; align-items: center; gap: 0.5rem; color: white; font-weight: 600;">
                        <i class="bi bi-briefcase-fill"></i> Applications to Your Jobs
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                @foreach($applications as $application)
                    <div style="padding: 1rem; border: 1px solid #eee; border-radius: 8px; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <div>
                                <h6 style="margin: 0 0 0.25rem 0; font-weight: 600;">{{ $application->job->title }}</h6>
                                <small style="color: #666;">
                                    <i class="bi bi-clock"></i> Applied {{ $application->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500;
                                {{ $application->status === 'pending' ? 'background: #fff3cd; color: #856404;' : '' }}
                                {{ $application->status === 'approved' ? 'background: #d4edda; color: #155724;' : '' }}
                                {{ $application->status === 'rejected' ? 'background: #f8d7da; color: #721c24;' : '' }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </div>
                        <a href="{{ route('employer.applications.show', $application->id) }}" 
                           style="display: inline-flex; align-items: center; gap: 0.5rem; color: #0066cc; text-decoration: none; font-size: 0.9rem;">
                            View Application <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endforeach
                </div>
            </div>
            @endif

            <!-- About Section -->
            @if($jobseeker->jobSeekerProfile && $jobseeker->jobSeekerProfile->bio)
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 1rem 1.5rem;">
                    <h5 style="margin: 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-person-circle"></i> About
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                    <p style="margin: 0; line-height: 1.6; color: #333;">{{ $jobseeker->jobSeekerProfile->bio }}</p>
                </div>
            </div>
            @endif

            <!-- Skills Section -->
            @if($jobseeker->skills && is_array($jobseeker->skills) && count($jobseeker->skills) > 0)
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 1rem 1.5rem;">
                    <h5 style="margin: 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-code-slash"></i> Skills
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                        @foreach($jobseeker->skills as $skill)
                            <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.5rem 1.25rem; border-radius: 20px; font-size: 0.9rem; font-weight: 500;">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Experience Section -->
            @if($jobseeker->jobSeekerProfile && $jobseeker->jobSeekerProfile->experience_years)
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 1rem 1.5rem;">
                    <h5 style="margin: 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-briefcase-fill"></i> Experience
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                    <p style="margin: 0; font-size: 1.1rem;">
                        <strong style="color: #fa709a; font-size: 1.5rem;">{{ $jobseeker->jobSeekerProfile->experience_years }} years</strong> of professional experience
                    </p>
                </div>
            </div>
            @endif

            <!-- Education Section -->
            @if($jobseeker->education && is_array($jobseeker->education) && count($jobseeker->education) > 0)
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); padding: 1rem 1.5rem;">
                    <h5 style="margin: 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-mortarboard-fill"></i> Education
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                @foreach($jobseeker->education as $edu)
                    <div style="margin-bottom: 1rem;">
                        <h6 style="margin: 0 0 0.25rem 0; font-weight: 600;">{{ $edu['degree'] ?? 'Degree' }}</h6>
                        <p style="margin: 0 0 0.25rem 0; color: #666;">{{ $edu['institution'] ?? 'Institution' }}</p>
                        @if(isset($edu['year']))
                            <small style="color: #999;">{{ $edu['year'] }}</small>
                        @endif
                    </div>
                @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            
            <!-- Resume Section -->
            @if($jobseeker->resume)
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); padding: 1rem 1.5rem;">
                    <h5 style="margin: 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Resume
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                <div style="text-align: center;">
                    <i class="bi bi-file-earmark-pdf" style="font-size: 3rem; color: #dc3545;"></i>
                    <p style="margin: 1rem 0; font-size: 0.9rem; word-break: break-all;">{{ basename($jobseeker->resume) }}</p>
                    <a href="{{ asset('storage/' . $jobseeker->resume) }}" 
                       class="btn btn-primary w-100 mb-2" target="_blank">
                        <i class="bi bi-eye me-2"></i>View Resume
                    </a>
                    <a href="{{ asset('storage/' . $jobseeker->resume) }}" 
                       class="btn btn-outline-primary w-100" download>
                        <i class="bi bi-download me-2"></i>Download
                    </a>
                </div>
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 1rem 1.5rem;">
                    <h5 style="margin: 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-bar-chart-fill"></i> Quick Stats
                    </h5>
                </div>
                <div style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                    <span style="color: #666;">Member Since</span>
                    <strong>{{ $jobseeker->created_at->format('M d, Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.75rem 0;">
                    <span style="color: #666;">Applications to Your Jobs</span>
                    <strong>{{ $applications->count() }}</strong>
                </div>
            </div>

            <!-- Actions -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 1.5rem;">
                <h5 style="margin: 0 0 1rem 0;">Actions</h5>
                <button onclick="window.print()" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="bi bi-printer me-2"></i>Print Profile
                </button>
                <button onclick="window.close()" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle me-2"></i>Close
                </button>
            </div>

        </div>
    </div>
</div>
@endsection
