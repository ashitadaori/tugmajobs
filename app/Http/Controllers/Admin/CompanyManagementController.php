<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CompanyManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $filter = $request->get('filter', 'all'); // all, admin, registered

        // Get admin-created companies
        $adminCompaniesQuery = Company::withCount('jobs')
            ->select([
                'id',
                'name',
                'email',
                'logo',
                'location',
                'is_active',
                'created_at',
                \DB::raw("'admin' as source"),
                \DB::raw("NULL as user_id"),
            ]);

        // Get employer-registered companies
        $employerCompaniesQuery = Employer::withCount('jobs')
            ->select([
                'id',
                'company_name as name',
                'business_email as email',
                'company_logo as logo',
                \DB::raw("CONCAT(COALESCE(city, ''), IF(city IS NOT NULL AND state IS NOT NULL, ', ', ''), COALESCE(state, '')) as location"),
                \DB::raw("CASE WHEN status = 'active' THEN 1 ELSE 0 END as is_active"),
                'created_at',
                \DB::raw("'registered' as source"),
                'user_id',
            ]);

        // Apply search filter
        if ($request->filled('search')) {
            $adminCompaniesQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('location', 'LIKE', '%' . $search . '%');
            });

            $employerCompaniesQuery->where(function($q) use ($search) {
                $q->where('company_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('business_email', 'LIKE', '%' . $search . '%')
                  ->orWhere('city', 'LIKE', '%' . $search . '%')
                  ->orWhere('state', 'LIKE', '%' . $search . '%');
            });
        }

        // Get results based on filter
        if ($filter === 'admin') {
            $adminCompanies = $adminCompaniesQuery->get();
            $employerCompanies = collect();
        } elseif ($filter === 'registered') {
            $adminCompanies = collect();
            $employerCompanies = $employerCompaniesQuery->get();
        } else {
            $adminCompanies = $adminCompaniesQuery->get();
            $employerCompanies = $employerCompaniesQuery->get();
        }

        // Merge and sort by created_at
        $allCompanies = $adminCompanies->concat($employerCompanies)
            ->sortByDesc('created_at');

        // Manual pagination
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $total = $allCompanies->count();
        $companies = new \Illuminate\Pagination\LengthAwarePaginator(
            $allCompanies->forPage($currentPage, $perPage),
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.company-management.index', compact('companies', 'filter'));
    }

    public function create()
    {
        return view('admin.company-management.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'founded_year' => 'nullable|string|max:4',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Generate unique slug (excluding soft-deleted companies)
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        
        // Check if slug exists and make it unique (only check non-deleted companies)
        while (Company::withoutTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $validated['slug'] = $slug;
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        $company = Company::create($validated);

        // Notify all jobseekers about the new company using Laravel's notification system
        $jobseekers = \App\Models\User::where('role', 'jobseeker')->get();
        
        foreach ($jobseekers as $jobseeker) {
            $jobseeker->notify(new \App\Notifications\NewCompanyJoinedNotification($company, 'standalone'));
        }

        return redirect()->route('admin.company-management.index')
            ->with('success', 'Company created successfully! All jobseekers have been notified.');
    }

    public function show(Company $company)
    {
        $company->load(['jobs' => function($query) {
            $query->with(['category', 'jobType'])
                  ->withCount('applications')
                  ->orderBy('created_at', 'DESC');
        }]);

        return view('admin.company-management.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.company-management.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'founded_year' => 'nullable|string|max:4',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Generate unique slug (excluding current company and soft-deleted companies)
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        
        // Check if slug exists (excluding current company and soft-deleted)
        while (Company::withoutTrashed()->where('slug', $slug)->where('id', '!=', $company->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $validated['slug'] = $slug;

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        $company->update($validated);

        return redirect()->route('admin.company-management.index')
            ->with('success', 'Company updated successfully!');
    }

    public function destroy(Company $company)
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()->route('admin.company-management.index')
            ->with('success', 'Company deleted successfully!');
    }
}
