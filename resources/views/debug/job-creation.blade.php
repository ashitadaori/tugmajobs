<!DOCTYPE html>
<html>
<head>
    <title>Job Creation Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; overflow: auto; }
        .btn { padding: 8px 16px; margin: 5px; background: #007bff; color: white; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Job Creation Debug Page</h1>
    
    <div class="section info">
        <h2>User Information</h2>
        @if(Auth::check())
            <p><strong>User ID:</strong> {{ Auth::user()->id }}</p>
            <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
            <p><strong>Role:</strong> {{ Auth::user()->role }}</p>
            <p><strong>KYC Status:</strong> {{ Auth::user()->kyc_status ?? 'Not set' }}</p>
            <p><strong>KYC Verified At:</strong> {{ Auth::user()->kyc_verified_at ?? 'Not verified' }}</p>
            <p><strong>Can Post Jobs:</strong> {{ Auth::user()->canPostJobs() ? 'YES' : 'NO' }}</p>
        @else
            <p class="error">User is not authenticated</p>
        @endif
    </div>

    <div class="section info">
        <h2>Route Information</h2>
        <p><strong>Current Route:</strong> {{ Route::currentRouteName() }}</p>
        <p><strong>Job Create URL:</strong> <a href="{{ route('employer.jobs.create') }}">{{ route('employer.jobs.create') }}</a></p>
        <p><strong>Job Store URL:</strong> {{ route('employer.jobs.store') }}</p>
    </div>

    <div class="section info">
        <h2>Form Data Availability</h2>
        @php
            $jobTypes = \App\Models\JobType::where('status', 1)->get();
            $categories = \App\Models\Category::where('status', 1)->get();
        @endphp
        <p><strong>Job Types Count:</strong> {{ $jobTypes->count() }}</p>
        <p><strong>Categories Count:</strong> {{ $categories->count() }}</p>
        
        @if($jobTypes->count() > 0)
            <h4>Available Job Types:</h4>
            <ul>
                @foreach($jobTypes as $type)
                    <li>{{ $type->name }} (ID: {{ $type->id }})</li>
                @endforeach
            </ul>
        @endif

        @if($categories->count() > 0)
            <h4>Available Categories:</h4>
            <ul>
                @foreach($categories as $category)
                    <li>{{ $category->name }} (ID: {{ $category->id }})</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="section info">
        <h2>Test Actions</h2>
        <a href="{{ route('employer.jobs.create') }}" class="btn">Go to Job Create Form</a>
        <a href="/test/job-creation-debug" class="btn">View JSON Debug Data</a>
        <a href="/test/verify-employer" class="btn">Verify Current Employer</a>
    </div>

    <div class="section info">
        <h2>Simple Job Creation Test</h2>
        <form action="{{ route('employer.jobs.store') }}" method="POST">
            @csrf
            <p>
                <label>Job Title:</label><br>
                <input type="text" name="title" value="Test Job" required style="width: 300px; padding: 5px;">
            </p>
            <p>
                <label>Description:</label><br>
                <textarea name="description" required style="width: 300px; padding: 5px;">Test job description</textarea>
            </p>
            <p>
                <label>Requirements:</label><br>
                <textarea name="requirements" required style="width: 300px; padding: 5px;">Test requirements</textarea>
            </p>
            <p>
                <label>Location:</label><br>
                <input type="text" name="location" value="Sta. Cruz" required style="width: 300px; padding: 5px;">
            </p>
            <p>
                <label>Job Type:</label><br>
                <select name="job_type_id" required style="width: 300px; padding: 5px;">
                    <option value="">Select Job Type</option>
                    @foreach($jobTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </p>
            <p>
                <label>Category:</label><br>
                <select name="category_id" required style="width: 300px; padding: 5px;">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </p>
            <p>
                <label>Vacancies:</label><br>
                <input type="number" name="vacancy" value="1" min="1" required style="width: 300px; padding: 5px;">
            </p>
            <p>
                <label>Experience Level:</label><br>
                <select name="experience_level" required style="width: 300px; padding: 5px;">
                    <option value="">Select Experience Level</option>
                    <option value="entry">Entry Level</option>
                    <option value="mid">Mid Level</option>
                    <option value="senior">Senior Level</option>
                    <option value="executive">Executive</option>
                </select>
            </p>
            <p>
                <button type="submit" class="btn" style="background: #28a745;">Submit Test Job</button>
            </p>
        </form>
    </div>

    @if(session('success'))
        <div class="section success">
            <h3>Success!</h3>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="section error">
            <h3>Error!</h3>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="section error">
            <h3>Validation Errors:</h3>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>
</html>
