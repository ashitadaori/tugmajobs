<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Job;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        // Get standalone companies
        $standaloneCompanies = \App\Models\Company::with(['jobs' => function($query) {
            $query->where('status', 1)->orderBy('created_at', 'desc');
        }])
        ->get()
        ->map(function($company) {
            return (object)[
                'id' => $company->id,
                'company_name' => $company->name,
                'company_description' => $company->description,
                'location' => $company->location ?? 'Sta. Cruz, Davao del Sur',
                'logo_url' => $company->logo ? 'storage/' . $company->logo : null,
                'created_at' => $company->created_at,
                'jobs' => $company->jobs,
                'type' => 'standalone'
            ];
        });

        // Get employer-based companies
        $employerCompanies = Employer::with(['user', 'jobs' => function($query) {
            $query->where('status', 1)->orderBy('created_at', 'desc');
        }])
        ->whereHas('jobs', function($query) {
            $query->where('status', 1);
        })
        ->get()
        ->map(function($employer) {
            return (object)[
                'id' => $employer->id,
                'company_name' => $employer->company_name ?? $employer->user->name,
                'company_description' => $employer->company_description,
                'location' => $employer->city ?? 'Sta. Cruz, Davao del Sur',
                'logo_url' => $employer->company_logo ? 'storage/' . $employer->company_logo : null,
                'created_at' => $employer->created_at,
                'jobs' => $employer->jobs,
                'type' => 'employer'
            ];
        });

        // Merge and sort by created_at
        $allCompanies = $standaloneCompanies->concat($employerCompanies)
            ->sortByDesc('created_at')
            ->values();

        // Manual pagination
        $perPage = 12;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedCompanies = $allCompanies->slice($offset, $perPage)->values();
        
        $companies = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedCompanies,
            $allCompanies->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('front.companies.index', compact('companies'));
    }

    public function show($id)
    {
        // Try to find as standalone company first
        $standaloneCompany = \App\Models\Company::find($id);
        
        if ($standaloneCompany) {
            $company = (object)[
                'id' => $standaloneCompany->id,
                'user_id' => null,
                'company_name' => $standaloneCompany->name,
                'company_description' => $standaloneCompany->description,
                'location' => $standaloneCompany->location ?? 'Sta. Cruz, Davao del Sur',
                'logo_url' => $standaloneCompany->logo ? 'storage/' . $standaloneCompany->logo : null,
                'website' => $standaloneCompany->website,
                'contact_email' => $standaloneCompany->email,
                'company_phone' => $standaloneCompany->phone,
                'industry' => $standaloneCompany->industry ?? null,
                'company_size' => $standaloneCompany->company_size ?? null,
                'founded_year' => $standaloneCompany->founded_year ?? null,
                'social_links' => $standaloneCompany->social_links ?? [],
                'created_at' => $standaloneCompany->created_at,
                'type' => 'standalone'
            ];

            $activeJobs = Job::where('company_id', $id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Try as employer profile
            $employer = Employer::with(['user'])->findOrFail($id);

            $company = (object)[
                'id' => $employer->id,
                'user_id' => $employer->user_id,
                'company_name' => $employer->company_name ?? $employer->user->name,
                'company_description' => $employer->company_description,
                'location' => $employer->city ?? 'Sta. Cruz, Davao del Sur',
                'logo_url' => $employer->company_logo ? 'storage/' . $employer->company_logo : null,
                'website' => $employer->company_website,
                'contact_email' => $employer->user->email,
                'company_phone' => $employer->business_phone,
                'industry' => $employer->industry ?? null,
                'company_size' => $employer->company_size ?? null,
                'founded_year' => $employer->founded_year ?? null,
                'social_links' => [
                    'linkedin' => $employer->linkedin_url,
                    'facebook' => $employer->facebook_url,
                    'twitter' => $employer->twitter_url,
                    'instagram' => $employer->instagram_url,
                ],
                'created_at' => $employer->created_at,
                'type' => 'employer'
            ];

            $activeJobs = Job::where('employer_id', $employer->user_id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('front.companies.show', compact('company', 'activeJobs'));
    }
} 