<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::orderBy('created_at', 'DESC')
            ->with('employer', 'applications')
            ->paginate(10);
            
        return view('admin.jobs.list', [
            'jobs' => $jobs
        ]);
    }

    public function create()
    {
        // Get only active categories and job types, ordered by name
        $categories = Category::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();
            
        $job_types = JobType::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();

        // Digos City locations
        $locations = [
            ['name' => 'Aplaya', 'lat' => 6.7489, 'lng' => 125.3714],
            ['name' => 'Binaton', 'lat' => 6.7623, 'lng' => 125.3897],
            ['name' => 'Cogon', 'lat' => 6.7512, 'lng' => 125.3567],
            ['name' => 'Colorado', 'lat' => 6.7534, 'lng' => 125.3678],
            ['name' => 'Dawis', 'lat' => 6.7567, 'lng' => 125.3645],
            ['name' => 'Dulangan', 'lat' => 6.7589, 'lng' => 125.3723],
            ['name' => 'Goma', 'lat' => 6.7612, 'lng' => 125.3834],
            ['name' => 'Igpit', 'lat' => 6.7645, 'lng' => 125.3756],
            ['name' => 'Mahayag', 'lat' => 6.7678, 'lng' => 125.3867],
            ['name' => 'Matti', 'lat' => 6.7523, 'lng' => 125.3589],
            ['name' => 'Poblacion', 'lat' => 6.7545, 'lng' => 125.3578],
            ['name' => 'San Jose', 'lat' => 6.7556, 'lng' => 125.3634],
            ['name' => 'San Miguel', 'lat' => 6.7578, 'lng' => 125.3712],
            ['name' => 'Sinawilan', 'lat' => 6.7634, 'lng' => 125.3845],
            ['name' => 'Soong', 'lat' => 6.7667, 'lng' => 125.3789],
            ['name' => 'Tres De Mayo', 'lat' => 6.7689, 'lng' => 125.3856]
        ];
            
        return view('admin.jobs.create', [
            'categories' => $categories,
            'job_types' => $job_types,
            'locations' => $locations
        ]);
    }

    public function save(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:200',
            'category' => 'required|exists:categories,id',
            'jobType' => 'required|exists:job_types,id',
            'vacancy' => 'required|integer|min:1',
            'location' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_address' => 'required|string|max:255',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|gt:salary_min',
            'experience_level' => 'required|in:entry,intermediate,expert',
            'description' => 'required|string|min:100',
            'requirements' => 'required|string|min:50',
            'benefits' => 'nullable|string',
            'company_name' => 'required|string|min:3|max:100',
            'company_website' => 'nullable|url|max:255',
            'is_draft' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create new job
            $job = new Job();
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->vacancies = $request->vacancy;
            $job->location = $request->location;
            $job->latitude = $request->latitude;
            $job->longitude = $request->longitude;
            $job->location_address = $request->location_address;
            $job->salary_min = $request->salary_min;
            $job->salary_max = $request->salary_max;
            $job->experience_level = $request->experience_level;
            $job->description = $request->description;
            $job->requirements = $request->requirements;
            $job->benefits = $request->benefits;
            $job->company_name = $request->company_name;
            $job->company_website = $request->company_website;
            $job->status = $request->is_draft ? 'draft' : 'active';
            $job->created_by = Auth::id();
            $job->save();

            return response()->json([
                'success' => true,
                'message' => $request->is_draft ? 'Job saved as draft successfully!' : 'Job posted successfully!',
                'redirect' => route('admin.jobs')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the job. Please try again.'
            ], 500);
        }
    }
}
