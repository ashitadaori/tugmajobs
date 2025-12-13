@extends('layouts.jobseeker')

@section('content')
<div style="padding: 20px; background: white; margin: 20px;">
    <h1 style="color: black; font-size: 24px; font-weight: bold;">Saved Jobs - Simple Test</h1>
    
    <div style="background: yellow; padding: 10px; margin: 10px 0; border: 2px solid orange;">
        <strong>Total Saved Jobs:</strong> {{ $savedJobs->total() }}
    </div>
    
    <div style="background: lightblue; padding: 10px; margin: 10px 0; border: 2px solid blue;">
        <strong>Current Page Items:</strong> {{ $savedJobs->count() }}
    </div>
    
    <hr style="border: 2px solid black;">
    
    <h2 style="color: black;">Jobs List:</h2>
    
    @forelse($savedJobs as $index => $savedJob)
        <div style="background: lightgreen; padding: 15px; margin: 10px 0; border: 2px solid green;">
            <h3 style="color: black;">Job #{{ $index + 1 }}</h3>
            <p style="color: black;"><strong>Saved Job ID:</strong> {{ $savedJob->id }}</p>
            <p style="color: black;"><strong>Job ID:</strong> {{ $savedJob->job_id }}</p>
            <p style="color: black;"><strong>Job Exists:</strong> {{ $savedJob->job ? 'YES' : 'NO' }}</p>
            
            @if($savedJob->job)
                <div style="background: white; padding: 10px; margin: 10px 0;">
                    <p style="color: black;"><strong>Job Title:</strong> {{ $savedJob->job->title }}</p>
                    <p style="color: black;"><strong>Company:</strong> {{ $savedJob->job->employer->name ?? 'N/A' }}</p>
                    <p style="color: black;"><strong>Location:</strong> {{ $savedJob->job->location }}</p>
                    <p style="color: black;"><strong>Job Type:</strong> {{ $savedJob->job->jobType->name ?? 'N/A' }}</p>
                    <p style="color: black;"><strong>Saved On:</strong> {{ $savedJob->created_at->format('M d, Y') }}</p>
                    
                    <a href="{{ route('jobDetail', $savedJob->job->id) }}" 
                       style="background: blue; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; margin-top: 10px;">
                        View Job Details
                    </a>
                </div>
            @else
                <p style="color: red; font-weight: bold;">Job has been deleted</p>
            @endif
        </div>
    @empty
        <div style="background: red; color: white; padding: 20px; margin: 10px 0; border: 2px solid darkred;">
            <h3>NO SAVED JOBS FOUND!</h3>
            <p>The @forelse loop found no items.</p>
        </div>
    @endforelse
    
    <hr style="border: 2px solid black;">
    
    <div style="margin-top: 20px;">
        {{ $savedJobs->links() }}
    </div>
</div>
@endsection