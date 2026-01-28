<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of all companies (employers)
     */
    public function index(Request $request)
    {
        // Clear any query cache
        \DB::connection()->disableQueryLog();
        
        $query = User::where('role', 'employer')
            ->withCount(['jobs' => function($q) {
                // Count all jobs
            }])
            ->with(['employerProfile' => function($q) {
                $q->select('*'); // Force select all columns including company_logo
            }]);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('employerProfile', function($profileQuery) use ($search) {
                      $profileQuery->where('company_name', 'LIKE', '%' . $search . '%');
                  });
            });
        }
        
        // Order by most recent
        $query->orderBy('created_at', 'DESC');
        
        // Paginate
        $companies = $query->paginate(15);
        
        // Force refresh each company's profile to get latest data
        foreach ($companies as $company) {
            if ($company->employerProfile) {
                $company->employerProfile->refresh();
            }
        }
        
        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Display the specified company and their jobs
     */
    public function show($id)
    {
        $company = User::where('role', 'employer')
            ->with(['employerProfile', 'jobs' => function($query) {
                $query->with(['category', 'jobType', 'applications'])
                      ->withCount('applications')
                      ->orderBy('created_at', 'DESC');
            }])
            ->findOrFail($id);
        
        return view('admin.companies.show', compact('company'));
    }
}
