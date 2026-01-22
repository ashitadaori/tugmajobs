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
        $standaloneCompanies = \App\Models\Company::with([
            'jobs' => function ($query) {
                $query->where('status', 1)->orderBy('created_at', 'desc');
            }
        ])
            ->get()
            ->map(function ($company) {
                return (object) [
                    'id' => $company->id,
                    'company_name' => $company->name,
                    'company_description' => $company->description,
                    'location' => $company->location ?? 'Sta. Cruz, Davao del Sur',
                    'logo_url' => $company->logo ? 'storage/' . $company->logo : null,
                    'created_at' => $company->created_at,
                    'jobs' => $company->jobs,
                    'type' => 'standalone',
                    'average_rating' => null,
                    'reviews_count' => 0
                ];
            });

        // Get employer-based companies
        $employerCompanies = Employer::with([
            'user',
            'jobs' => function ($query) {
                $query->where('status', 1)->orderBy('created_at', 'desc');
            }
        ])
            ->whereHas('jobs', function ($query) {
                $query->where('status', 1);
            })
            ->get()
            ->map(function ($employer) {
                $reviewsCount = \App\Models\Review::where('employer_id', $employer->user_id)
                    ->where('review_type', 'company')
                    ->count();
                $averageRating = \App\Models\Review::getCompanyAverageRating($employer->user_id);

                return (object) [
                    'id' => $employer->id,
                    'company_name' => $employer->company_name ?? $employer->user->name,
                    'company_description' => $employer->company_description,
                    'location' => $employer->city ?? 'Sta. Cruz, Davao del Sur',
                    'logo_url' => $employer->company_logo ? 'storage/' . $employer->company_logo : null,
                    'created_at' => $employer->created_at,
                    'jobs' => $employer->jobs,
                    'type' => 'employer',
                    'average_rating' => $averageRating ? round($averageRating, 1) : null,
                    'reviews_count' => $reviewsCount
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
        // Try to find as standalone company first (by ID or slug)
        $standaloneCompany = \App\Models\Company::where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if ($standaloneCompany) {
            $company = (object) [
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
                'type' => 'standalone',
                'latitude' => $standaloneCompany->latitude ?? null,
                'longitude' => $standaloneCompany->longitude ?? null,
            ];

            $activeJobs = Job::where('company_id', $standaloneCompany->id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('front.companies.show', compact('company', 'activeJobs'));
        }

        // Try as employer profile by user_id first (this is what HomeController uses)
        $employer = Employer::with(['user'])->where('user_id', $id)->first();

        // If not found by user_id, try by employer id
        if (!$employer) {
            $employer = Employer::with(['user'])->find($id);
        }

        if (!$employer) {
            abort(404, 'Company not found');
        }

        // Increment profile views
        $employer->increment('profile_views');

        $company = (object) [
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
            'type' => 'employer',
            'latitude' => $employer->latitude ?? null,
            'longitude' => $employer->longitude ?? null,
        ];

        $activeJobs = Job::where('employer_id', $employer->user_id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('front.companies.show', compact('company', 'activeJobs'));
    }
}