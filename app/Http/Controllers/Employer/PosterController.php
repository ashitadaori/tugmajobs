<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Poster;
use App\Models\PosterTemplate;
use App\Models\Job;
use App\Models\Employer;
use App\Services\PosterMyWallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PosterController extends Controller
{
    protected PosterMyWallService $posterMyWallService;

    public function __construct(PosterMyWallService $posterMyWallService)
    {
        $this->posterMyWallService = $posterMyWallService;
    }

    /**
     * Display list of employer's posters
     */
    public function index()
    {
        $posters = Poster::with(['template', 'job'])
            ->byEmployer(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('front.account.employer.posters.index', compact('posters'));
    }

    /**
     * Show create form - redirects to template browser
     */
    public function create(Request $request)
    {
        // Check if PosterMyWall is configured
        if (!$this->posterMyWallService->isConfigured()) {
            return redirect()->route('employer.posters.index')
                ->with('error', 'Poster builder is not configured. Please contact support.');
        }

        // Get employer's active jobs for the form
        $jobs = Job::where('user_id', Auth::id())
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get employer profile for default values
        $employerProfile = Employer::where('user_id', Auth::id())->first();

        // Pre-fill from job if job_id is provided
        $selectedJob = null;
        if ($request->has('job_id')) {
            $selectedJob = Job::where('id', $request->job_id)
                ->where('user_id', Auth::id())
                ->first();
        }

        return view('front.account.employer.posters.create', compact(
            'jobs',
            'employerProfile',
            'selectedJob'
        ));
    }

    /**
     * Browse PosterMyWall templates
     */
    public function templates(Request $request)
    {
        $query = $request->get('q', 'hiring');
        $category = $request->get('category', 'hiring');
        $page = $request->get('page', 1);

        $result = $this->posterMyWallService->searchTemplates($query, [
            'category' => $category,
            'page' => $page,
            'per_page' => 20,
        ]);

        $templates = $result['success'] ? ($result['data']['templates'] ?? []) : [];
        $pagination = $result['success'] ? ($result['data']['pagination'] ?? null) : null;

        // Get categories for filter
        $categoriesResult = $this->posterMyWallService->getCategories();
        $categories = $categoriesResult['success'] ? ($categoriesResult['data'] ?? []) : [];

        if ($request->ajax()) {
            return response()->json([
                'success' => $result['success'],
                'templates' => $templates,
                'pagination' => $pagination,
            ]);
        }

        return view('front.account.employer.posters.templates', compact(
            'templates',
            'categories',
            'query',
            'category',
            'pagination'
        ));
    }

    /**
     * Select a PosterMyWall template and create a design
     */
    public function selectTemplate(Request $request, string $templateId)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'nullable|exists:jobs,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }

        // Get template details
        $templateResult = $this->posterMyWallService->getTemplate($templateId);

        if (!$templateResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load template. Please try again.',
            ]);
        }

        $templateData = $templateResult['data'];

        // Get employer profile for customizations
        $employerProfile = Employer::where('user_id', Auth::id())->first();

        // Get job details if provided
        $job = null;
        if ($request->job_id) {
            $job = Job::where('id', $request->job_id)
                ->where('user_id', Auth::id())
                ->first();
        }

        // Create design with customizations
        $customizations = [
            'company_name' => $employerProfile?->company_name ?? Auth::user()->name,
            'job_title' => $job?->title ?? '',
            'location' => $job?->location ?? $employerProfile?->address ?? '',
            'contact_email' => $employerProfile?->email ?? Auth::user()->email,
        ];

        $designResult = $this->posterMyWallService->createDesign($templateId, $customizations);

        if (!$designResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create design. Please try again.',
            ]);
        }

        $designData = $designResult['data'];

        // Create poster record
        $poster = Poster::create([
            'created_by' => Auth::id(),
            'employer_id' => Auth::id(),
            'job_id' => $request->job_id,
            'job_title' => $job?->title ?? 'New Poster',
            'requirements' => $job?->description ?? '',
            'company_name' => $employerProfile?->company_name ?? Auth::user()->name,
            'contact_email' => $employerProfile?->email ?? Auth::user()->email,
            'contact_phone' => $employerProfile?->phone ?? null,
            'location' => $job?->location ?? $employerProfile?->address ?? '',
            'salary_range' => $job?->salary ?? null,
            'employment_type' => $job?->job_type ?? null,
            'poster_type' => 'employer',
            'source' => 'postermywall',
            'pmw_template_id' => $templateId,
            'pmw_design_id' => $designData['id'] ?? null,
            'pmw_preview_url' => $designData['preview_url'] ?? $templateData['preview_url'] ?? null,
            'pmw_customizations' => $customizations,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Design created successfully!',
            'poster_id' => $poster->id,
            'redirect' => route('employer.posters.editor', $poster->id),
        ]);
    }

    /**
     * Show the PosterMyWall editor for a design
     */
    public function editor($id)
    {
        $poster = Poster::byEmployer(Auth::id())->findOrFail($id);

        if (!$poster->isPosterMyWall()) {
            return redirect()->route('employer.posters.edit', $id);
        }

        $editorUrl = $this->posterMyWallService->getEditorUrl($poster->pmw_design_id, [
            'callback_url' => route('employer.posters.callback'),
        ]);

        return view('front.account.employer.posters.editor', compact('poster', 'editorUrl'));
    }

    /**
     * Handle callback from PosterMyWall editor
     */
    public function callback(Request $request)
    {
        $designId = $request->get('design_id');
        $action = $request->get('action', 'save');

        if (!$designId) {
            return response()->json(['success' => false, 'message' => 'Design ID missing']);
        }

        $poster = Poster::where('pmw_design_id', $designId)
            ->byEmployer(Auth::id())
            ->first();

        if (!$poster) {
            return response()->json(['success' => false, 'message' => 'Poster not found']);
        }

        if ($action === 'save' || $action === 'export') {
            // Get updated design details
            $designResult = $this->posterMyWallService->getDesign($designId);

            if ($designResult['success']) {
                $poster->update([
                    'pmw_preview_url' => $designResult['data']['preview_url'] ?? $poster->pmw_preview_url,
                    'pmw_download_url' => $designResult['data']['download_url'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'redirect' => route('employer.posters.preview', $poster->id),
        ]);
    }

    /**
     * Store a new poster
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:poster_templates,id',
            'job_id' => 'nullable|exists:jobs,id',
            'job_title' => 'required|string|max:255',
            'requirements' => 'required|string',
            'company_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:50',
            'deadline' => 'nullable|date|after:today',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        // Verify job belongs to employer if provided
        if ($request->job_id) {
            $job = Job::where('id', $request->job_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$job) {
                return response()->json([
                    'status' => false,
                    'errors' => ['job_id' => ['Invalid job selected.']],
                ]);
            }
        }

        // Get employer profile for company logo
        $employerProfile = Employer::where('user_id', Auth::id())->first();

        $poster = Poster::create([
            'template_id' => $request->template_id,
            'created_by' => Auth::id(),
            'employer_id' => Auth::id(),
            'job_id' => $request->job_id,
            'job_title' => $request->job_title,
            'requirements' => $request->requirements,
            'company_name' => $request->company_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'location' => $request->location,
            'salary_range' => $request->salary_range,
            'employment_type' => $request->employment_type,
            'deadline' => $request->deadline,
            'company_logo' => $employerProfile?->company_logo,
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'poster_type' => 'employer',
        ]);

        // Mark template as used for rotation
        $template = PosterTemplate::find($request->template_id);
        $template->markAsUsed();

        session()->flash('success', 'Poster created successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Poster created successfully!',
            'redirect' => route('employer.posters.preview', $poster->id),
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $poster = Poster::with('template')
            ->byEmployer(Auth::id())
            ->findOrFail($id);

        $templates = PosterTemplate::active()->ordered()->get();

        $jobs = Job::where('user_id', Auth::id())
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $employerProfile = Employer::where('user_id', Auth::id())->first();

        return view('front.account.employer.posters.edit', compact(
            'poster',
            'templates',
            'jobs',
            'employerProfile'
        ));
    }

    /**
     * Update poster
     */
    public function update(Request $request, $id)
    {
        $poster = Poster::byEmployer(Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:poster_templates,id',
            'job_id' => 'nullable|exists:jobs,id',
            'job_title' => 'required|string|max:255',
            'requirements' => 'required|string',
            'company_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        // Verify job belongs to employer if provided
        if ($request->job_id) {
            $job = Job::where('id', $request->job_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$job) {
                return response()->json([
                    'status' => false,
                    'errors' => ['job_id' => ['Invalid job selected.']],
                ]);
            }
        }

        $poster->update([
            'template_id' => $request->template_id,
            'job_id' => $request->job_id,
            'job_title' => $request->job_title,
            'requirements' => $request->requirements,
            'company_name' => $request->company_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'location' => $request->location,
            'salary_range' => $request->salary_range,
            'employment_type' => $request->employment_type,
            'deadline' => $request->deadline,
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
        ]);

        session()->flash('success', 'Poster updated successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Poster updated successfully!',
            'redirect' => route('employer.posters.preview', $poster->id),
        ]);
    }

    /**
     * Preview poster
     */
    public function preview($id)
    {
        $poster = Poster::with('template')
            ->byEmployer(Auth::id())
            ->findOrFail($id);

        // Handle PosterMyWall posters
        if ($poster->isPosterMyWall()) {
            return view('front.account.employer.posters.pmw-preview', compact('poster'));
        }

        $templateSlug = $poster->template->slug;

        // Try to load enhanced employer template first
        $employerTemplateView = "front.account.employer.posters.templates.{$templateSlug}";
        $adminTemplateView = "admin.posters.templates.{$templateSlug}";

        if (view()->exists($employerTemplateView)) {
            return view($employerTemplateView, compact('poster'));
        }

        if (view()->exists($adminTemplateView)) {
            return view($adminTemplateView, [
                'poster' => $poster,
                'isEmployer' => true
            ]);
        }

        return view('front.account.employer.posters.preview', compact('poster'));
    }

    /**
     * Download poster as PDF or image
     */
    public function download($id, Request $request)
    {
        $poster = Poster::with('template')
            ->byEmployer(Auth::id())
            ->findOrFail($id);

        $format = $request->get('format', 'pdf');

        // Handle PosterMyWall posters
        if ($poster->isPosterMyWall()) {
            // If we have a cached download URL, redirect to it
            if ($poster->pmw_download_url) {
                return redirect($poster->pmw_download_url);
            }

            // Otherwise, request export from PosterMyWall
            $exportResult = $this->posterMyWallService->exportDesign(
                $poster->pmw_design_id,
                $format,
                ['quality' => 'high']
            );

            if ($exportResult['success'] && isset($exportResult['data']['download_url'])) {
                // Cache the download URL
                $poster->update(['pmw_download_url' => $exportResult['data']['download_url']]);
                return redirect($exportResult['data']['download_url']);
            }

            // Try to get direct download URL
            $downloadResult = $this->posterMyWallService->getDesignDownloadUrl($poster->pmw_design_id, $format);

            if ($downloadResult['success'] && isset($downloadResult['data']['url'])) {
                return redirect($downloadResult['data']['url']);
            }

            return back()->with('error', 'Failed to download poster. Please try again.');
        }

        // Handle local posters with PDF generation
        $templateSlug = $poster->template->slug;
        $isPdf = true;
        $isEmployer = true;

        // Use the admin template view for PDF generation (consistent with preview)
        $adminTemplateView = "admin.posters.templates.{$templateSlug}";

        if (view()->exists($adminTemplateView)) {
            $pdf = Pdf::loadView($adminTemplateView, [
                'poster' => $poster,
                'isPdf' => $isPdf,
                'isEmployer' => $isEmployer
            ]);
        } else {
            // Fallback to the employer preview view with embedded templates
            $pdf = Pdf::loadView('front.account.employer.posters.preview', [
                'poster' => $poster,
                'isPdf' => $isPdf,
                'isEmployer' => $isEmployer
            ]);
        }

        // Set paper size - Letter size scaled to 4:5 ratio (8x10 inches at 72 DPI = 576x720 points)
        $pdf->setPaper([0, 0, 576, 720], 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'Helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'dpi' => 72,
        ]);

        $filename = 'hiring-poster-' . str_replace(' ', '-', strtolower($poster->job_title)) . '-' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Delete poster
     */
    public function destroy($id)
    {
        $poster = Poster::byEmployer(Auth::id())->find($id);

        if (!$poster) {
            return response()->json([
                'status' => false,
                'message' => 'Poster not found.',
            ]);
        }

        $poster->delete();

        session()->flash('success', 'Poster deleted successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Poster deleted successfully!',
        ]);
    }

    /**
     * Quick create poster from job
     */
    public function createFromJob($jobId)
    {
        $job = Job::where('id', $jobId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return redirect()->route('employer.posters.create', ['job_id' => $job->id]);
    }

    /**
     * Duplicate an existing poster
     */
    public function duplicate($id)
    {
        $poster = Poster::byEmployer(Auth::id())->findOrFail($id);

        $newPoster = $poster->replicate();
        $newPoster->job_title = $poster->job_title . ' (Copy)';
        $newPoster->created_at = now();
        $newPoster->save();

        session()->flash('success', 'Poster duplicated successfully!');

        return redirect()->route('employer.posters.edit', $newPoster->id);
    }
}
