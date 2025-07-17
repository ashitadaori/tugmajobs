<?php

namespace App\Http\Controllers;

use App\Models\EmployerProfile;
use App\Models\Job;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = EmployerProfile::with(['user', 'jobs' => function($query) {
            $query->where('status', 'active')
                  ->orderBy('created_at', 'desc');
        }])
        ->whereHas('jobs', function($query) {
            $query->where('status', 'active');
        })
        ->paginate(12);

        return view('front.companies.index', compact('companies'));
    }

    public function show($id)
    {
        $company = EmployerProfile::with(['user', 'jobs' => function($query) {
            $query->where('status', 'active')
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $activeJobs = $company->jobs()->where('status', 'active')->paginate(10);

        return view('front.companies.show', compact('company', 'activeJobs'));
    }
} 