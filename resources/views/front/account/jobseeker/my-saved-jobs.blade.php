@extends('layouts.jobseeker')

@section('content')
<div style="padding: 30px; max-width: 1200px; margin: 0 auto;">
    
    <!-- Header Section -->
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <h1 style="color: #1e293b; font-size: 28px; font-weight: 700; margin: 0 0 10px 0;">
                    <i class="fas fa-bookmark" style="color: #6366f1; margin-right: 10px;"></i>
                    My Saved Jobs
                </h1>
                <p style="color: #64748b; margin: 0; font-size: 14px;">
                    <a href="{{ route('account.dashboard') }}" style="color: #6366f1; text-decoration: none;">Dashboard</a>
                    <span style="margin: 0 8px; color: #cbd5e1;">/</span>
                    <span style="color: #64748b;">Saved Jobs</span>
                </p>
            </div>
            <div style="background: #f1f5f9; padding: 12px 20px; border-radius: 8px;">
                <span style="color: #1e293b; font-weight: 600; font-size: 18px;">{{ $savedJobs->total() }}</span>
                <span style="color: #64748b; font-size: 14px; margin-left: 5px;">saved jobs</span>
            </div>
        </div>
    </div>

    @php
        $jobsArray = $savedJobs->items();
        $hasJobs = count($jobsArray) > 0;
    @endphp

    @if($hasJobs)
        <!-- Jobs List -->
        @foreach($jobsArray as $savedJob)
            @if($savedJob->job)
                <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; transition: transform 0.2s, box-shadow 0.2s;" 
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" 
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                    
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <!-- Company Logo -->
                        <div style="flex-shrink: 0;">
                            @if($savedJob->job->employer && $savedJob->job->employer->image)
                                <img src="{{ asset('storage/' . $savedJob->job->employer->image) }}" 
                                     alt="Company Logo" 
                                     style="width: 70px; height: 70px; border-radius: 12px; object-fit: cover; border: 2px solid #e2e8f0;">
                            @else
                                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; font-weight: 700; border: 2px solid #e2e8f0;">
                                    {{ substr($savedJob->job->employer->name ?? 'C', 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <!-- Job Details -->
                        <div style="flex: 1; min-width: 300px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 600;">
                                <a href="{{ route('jobDetail', $savedJob->job->id) }}" 
                                   style="color: #1e293b; text-decoration: none; transition: color 0.2s;"
                                   onmouseover="this.style.color='#6366f1';"
                                   onmouseout="this.style.color='#1e293b';">
                                    {{ $savedJob->job->title }}
                                </a>
                            </h3>
                            
                            <p style="color: #6366f1; font-weight: 500; margin: 0 0 12px 0; font-size: 15px;">
                                <i class="fas fa-building" style="margin-right: 6px;"></i>
                                {{ $savedJob->job->employer->name ?? 'Company' }}
                            </p>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 15px; color: #64748b; font-size: 14px;">
                                <span>
                                    <i class="fas fa-map-marker-alt" style="color: #6366f1; margin-right: 5px;"></i>
                                    {{ $savedJob->job->location }}
                                </span>
                                <span>
                                    <i class="fas fa-briefcase" style="color: #6366f1; margin-right: 5px;"></i>
                                    {{ $savedJob->job->jobType->name ?? 'N/A' }}
                                </span>
                                @if($savedJob->job->salary_min && $savedJob->job->salary_max)
                                    <span>
                                        <i class="fas fa-peso-sign" style="color: #10b981; margin-right: 5px;"></i>
                                        ₱{{ number_format($savedJob->job->salary_min) }} - ₱{{ number_format($savedJob->job->salary_max) }}
                                    </span>
                                @endif
                                <span>
                                    <i class="fas fa-clock" style="color: #6366f1; margin-right: 5px;"></i>
                                    Saved {{ $savedJob->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end; justify-content: center;">
                            <a href="{{ route('jobDetail', $savedJob->job->id) }}" 
                               style="background: #6366f1; color: white; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; white-space: nowrap;"
                               onmouseover="this.style.background='#4f46e5';"
                               onmouseout="this.style.background='#6366f1';">
                                <i class="fas fa-eye"></i>
                                View Job
                            </a>
                            <button onclick="removeJob({{ $savedJob->job->id }})" 
                                    style="background: white; color: #dc2626; padding: 10px 24px; border-radius: 8px; border: 2px solid #dc2626; font-weight: 500; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; white-space: nowrap;"
                                    onmouseover="this.style.background='#dc2626'; this.style.color='white';"
                                    onmouseout="this.style.background='white'; this.style.color='#dc2626';">
                                <i class="fas fa-trash"></i>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Pagination -->
        @if($savedJobs->hasPages())
            <div style="margin-top: 30px; display: flex; justify-content: center;">
                {{ $savedJobs->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div style="background: white; padding: 60px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 80px; color: #e2e8f0; margin-bottom: 20px;">
                <i class="fas fa-bookmark"></i>
            </div>
            <h3 style="color: #1e293b; font-size: 24px; font-weight: 600; margin: 0 0 15px 0;">
                No Saved Jobs Yet
            </h3>
            <p style="color: #64748b; font-size: 16px; margin: 0 0 30px 0; max-width: 500px; margin-left: auto; margin-right: auto;">
                Start saving jobs you're interested in to view them here later. Click the bookmark icon on any job listing to save it.
            </p>
            <a href="{{ route('jobs') }}" 
               style="background: #6366f1; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; display: inline-flex; align-items: center; gap: 10px; transition: background 0.2s;"
               onmouseover="this.style.background='#4f46e5';"
               onmouseout="this.style.background='#6366f1';">
                <i class="fas fa-search"></i>
                Browse Jobs
            </a>
        </div>
    @endif

</div>

<script>
function removeJob(jobId) {
    if (!confirm('Are you sure you want to remove this job from your saved list?')) {
        return;
    }
    
    fetch('{{ route("account.saved-jobs.destroy") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show updated list
            window.location.reload();
        } else {
            alert('Failed to remove job. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
@endsection
