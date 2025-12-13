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
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('admin.posters.index', compact('posters'));
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

        // Try to load template-specific view
        $templateView = "admin.posters.templates.{$templateSlug}";

        if (view()->exists($templateView)) {
            return view($templateView, compact('poster'));
        }

        // Fallback to default preview
        return view('admin.posters.preview', compact('poster'));
    }

    /**
     * Download poster as PDF
     */
    public function download($id)
    {
        $poster = Poster::with('template')->findOrFail($id);
        $templateSlug = $poster->template->slug;
        $isPdf = true; // Flag to hide action buttons in PDF

        // Try to load template-specific view
        $templateView = "admin.posters.templates.{$templateSlug}";

        if (view()->exists($templateView)) {
            $pdf = Pdf::loadView($templateView, compact('poster', 'isPdf'));
        } else {
            $pdf = Pdf::loadView('admin.posters.preview', compact('poster', 'isPdf'));
        }

        // Set paper size to 4:5 portrait ratio for social media (Facebook/Instagram)
        // 432x540 points (6x7.5 inches) - perfect 4:5 ratio like the reference design
        $pdf->setPaper([0, 0, 432, 540], 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'Helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'dpi' => 150,
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
