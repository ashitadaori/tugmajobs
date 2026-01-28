<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poster;
use App\Models\PosterTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PosterController extends Controller
{
    /**
     * Display list of all posters
     */
    public function index()
    {
        $posters = Poster::with(['template', 'creator'])
            ->adminPosters()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $stats = [
            'total' => Poster::adminPosters()->count(),
            'thisMonth' => Poster::adminPosters()->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.posters.index', compact('posters', 'stats'));
    }

    /**
     * Show create form with auto-selected template (rotation)
     */
    public function create()
    {
        // Get the next template in rotation
        $template = PosterTemplate::getNextTemplate();

        if (!$template) {
            return redirect()->route('admin.posters.index')
                ->with('error', 'No active poster templates available. Please add templates first.');
        }

        $templates = PosterTemplate::active()->ordered()->get();

        return view('admin.posters.create', compact('template', 'templates'));
    }

    /**
     * Store a new poster
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:poster_templates,id',
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

        $poster = Poster::create([
            'template_id' => $request->template_id,
            'created_by' => Auth::id(),
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
            'poster_type' => 'admin',
        ]);

        // Mark template as used for rotation
        $template = PosterTemplate::find($request->template_id);
        $template->markAsUsed();

        session()->flash('success', 'Poster created successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Poster created successfully!',
            'redirect' => route('admin.posters.preview', $poster->id),
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $poster = Poster::with('template')->findOrFail($id);
        $templates = PosterTemplate::active()->ordered()->get();

        return view('admin.posters.edit', compact('poster', 'templates'));
    }

    /**
     * Update poster
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:poster_templates,id',
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

        $poster = Poster::findOrFail($id);
        $poster->update([
            'template_id' => $request->template_id,
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
            'redirect' => route('admin.posters.preview', $poster->id),
        ]);
    }

    /**
     * Preview poster with template
     */
    public function preview($id)
    {
        $poster = Poster::with('template')->findOrFail($id);
        $templateSlug = $poster->template->slug;
        $isEmployer = false;

        // Try to load template-specific view
        $templateView = "admin.posters.templates.{$templateSlug}";

        if (view()->exists($templateView)) {
            return view($templateView, compact('poster', 'isEmployer'));
        }

        // Fallback to default preview
        return view('admin.posters.preview', compact('poster', 'isEmployer'));
    }

    /**
     * Download poster as PDF
     */
    public function download($id)
    {
        $poster = Poster::with('template')->findOrFail($id);
        $templateSlug = $poster->template->slug;
        $isPdf = true; // Flag to hide action buttons in PDF
        $isEmployer = false;

        // Try to load template-specific view
        $templateView = "admin.posters.templates.{$templateSlug}";

        if (view()->exists($templateView)) {
            $pdf = Pdf::loadView($templateView, compact('poster', 'isPdf', 'isEmployer'));
        } else {
            $pdf = Pdf::loadView('admin.posters.preview', compact('poster', 'isPdf', 'isEmployer'));
        }

        // Set paper size - 4:5 ratio (8x10 inches at 72 DPI = 576x720 points)
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
        $poster = Poster::find($id);

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
     * Manage poster templates (list)
     */
    public function templates()
    {
        $templates = PosterTemplate::ordered()->paginate(10);
        return view('admin.posters.templates.index', compact('templates'));
    }

    /**
     * Toggle template status
     */
    public function toggleTemplate($id)
    {
        $template = PosterTemplate::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        $status = $template->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => true,
            'message' => "Template {$status} successfully!",
            'is_active' => $template->is_active,
        ]);
    }
}
