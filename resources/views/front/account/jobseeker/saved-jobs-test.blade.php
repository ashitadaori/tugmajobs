@extends('layouts.jobseeker')

@section('content')
<div style="padding: 40px; background: red; color: white; margin: 20px; border: 5px solid black;">
    <h1 style="font-size: 48px;">🚨 TEST VIEW IS LOADING! 🚨</h1>
    <p style="font-size: 24px;">If you can see this, the view is working!</p>
    <p style="font-size: 20px;">Total Saved Jobs: {{ $savedJobs->total() }}</p>
    <p style="font-size: 20px;">Count: {{ $savedJobs->count() }}</p>
</div>

<div style="padding: 20px; background: white; margin: 20px; border: 3px solid blue;">
    <h2 style="color: black; font-size: 32px;">Jobs List (No @forelse):</h2>
    
    @php
        $jobsArray = $savedJobs->items();
    @endphp
    
    @foreach($jobsArray as $index => $savedJob)
        <div style="background: yellow; padding: 20px; margin: 10px 0; border: 3px solid orange;">
            <h3 style="color: black; font-size: 24px;">Job {{ $index + 1 }}</h3>
            <p style="color: black; font-size: 18px;"><strong>Saved Job ID:</strong> {{ $savedJob->id }}</p>
            <p style="color: black; font-size: 18px;"><strong>Job ID:</strong> {{ $savedJob->job_id }}</p>
            
            @if($savedJob->job)
                <p style="color: green; font-size: 18px;"><strong>✓ Job Title:</strong> {{ $savedJob->job->title }}</p>
                <p style="color: green; font-size: 18px;"><strong>✓ Company:</strong> {{ $savedJob->job->employer->name ?? 'N/A' }}</p>
                <p style="color: green; font-size: 18px;"><strong>✓ Location:</strong> {{ $savedJob->job->location }}</p>
            @else
                <p style="color: red; font-size: 18px;"><strong>✗ Job has been deleted</strong></p>
            @endif
        </div>
    @endforeach
    
    @if(count($jobsArray) == 0)
        <div style="background: red; color: white; padding: 30px; font-size: 24px;">
            NO JOBS FOUND!
        </div>
    @endif
</div>
@endsection