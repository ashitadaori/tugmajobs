<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeData;
use App\Models\ResumeTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ResumeBuilderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $resumes = Resume::where('user_id', $user->id)->with('template')->latest()->get();
        $templates = ResumeTemplate::where('is_active', true)->orderBy('display_order')->get();
        
        return view('front.account.resume-builder.index', compact('resumes', 'templates'));
    }

    public function create(Request $request)
    {
        $templateId = $request->get('template');
        $template = ResumeTemplate::findOrFail($templateId);
        $user = Auth::user();

        // Load jobseeker profile for auto-fill
        $jobseekerProfile = $user->jobSeekerProfile;

        // Auto-fill personal info from jobseeker profile if it exists, otherwise from user
        $personalInfo = [
            'name' => $jobseekerProfile && $jobseekerProfile->first_name
                ? trim($jobseekerProfile->first_name . ' ' . $jobseekerProfile->last_name)
                : $user->name,
            'email' => $user->email,
            'phone' => $jobseekerProfile && $jobseekerProfile->phone
                ? $jobseekerProfile->phone
                : ($user->mobile ?? ''),
            'address' => $jobseekerProfile && $jobseekerProfile->current_address
                ? $jobseekerProfile->current_address
                : ($user->address ?? ''),
            'linkedin' => $jobseekerProfile && $jobseekerProfile->linkedin_url
                ? $jobseekerProfile->linkedin_url
                : ($user->linkedin ?? ''),
            'website' => $jobseekerProfile && $jobseekerProfile->portfolio_url
                ? $jobseekerProfile->portfolio_url
                : ($user->website ?? ''),
            'job_title' => $jobseekerProfile && $jobseekerProfile->current_job_title
                ? $jobseekerProfile->current_job_title
                : ($user->job_title ?? $user->designation ?? ''),
        ];

        // Auto-fill education from jobseeker profile (if exists)
        $education = [];
        if ($jobseekerProfile && !empty($jobseekerProfile->education) && is_array($jobseekerProfile->education)) {
            $education = $jobseekerProfile->education;
        } elseif (!empty($user->education) && is_array($user->education)) {
            $education = $user->education;
        }

        // Auto-fill skills from jobseeker profile (if exists)
        $skills = [];
        if ($jobseekerProfile && !empty($jobseekerProfile->skills) && is_array($jobseekerProfile->skills)) {
            $skills = $jobseekerProfile->skills;
        } elseif (!empty($user->skills) && is_array($user->skills)) {
            $skills = $user->skills;
        }

        // Auto-fill professional summary from jobseeker profile bio
        $professionalSummary = '';
        if ($jobseekerProfile && $jobseekerProfile->professional_summary) {
            $professionalSummary = $jobseekerProfile->professional_summary;
        } elseif ($user->bio) {
            $professionalSummary = $user->bio;
        }

        return view('front.account.resume-builder.create', compact('template', 'personalInfo', 'education', 'skills', 'professionalSummary'));
    }

    public function store(Request $request)
    {
        // DEBUG: Log what we're receiving
        \Log::info('Resume Store - Raw Input:', [
            'work_experience' => $request->work_experience,
            'education' => $request->education,
            'skills' => $request->skills,
            'languages' => $request->languages,
        ]);
        
        $validated = $request->validate([
            'template_id' => 'required|exists:resume_templates,id',
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'job_title' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'professional_summary' => 'nullable|string|max:1000',
            'work_experience' => 'nullable|string',
            'education' => 'nullable|string',
            'skills' => 'nullable|string',
            'certifications' => 'nullable|string',
            'languages' => 'nullable|string',
            'projects' => 'nullable|string',
        ]);

        try {
            $resume = Resume::create([
                'user_id' => Auth::id(),
                'template_id' => $validated['template_id'],
                'title' => $validated['title'],
            ]);

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('resume-photos', 'public');
            }
            
            // Decode JSON data safely
            $workExperience = $request->work_experience ? json_decode($request->work_experience, true) : [];
            $education = $request->education ? json_decode($request->education, true) : [];
            $skills = $request->skills ? json_decode($request->skills, true) : [];
            $certifications = $request->certifications ? json_decode($request->certifications, true) : [];
            $languages = $request->languages ? json_decode($request->languages, true) : [];
            $projects = $request->projects ? json_decode($request->projects, true) : [];
            
            ResumeData::create([
                'resume_id' => $resume->id,
                'personal_info' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'website' => $request->website,
                    'job_title' => $request->job_title,
                    'photo' => $photoPath,
                ],
                'professional_summary' => $request->professional_summary,
                'work_experience' => $workExperience,
                'education' => $education,
                'skills' => $skills,
                'certifications' => $certifications,
                'languages' => $languages,
                'projects' => $projects,
            ]);

            return redirect()->route('account.resume-builder.index')
                ->with('success', 'Resume created successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Resume creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create resume. Please try again.');
        }
    }

    public function edit($id)
    {
        $resume = Resume::where('user_id', Auth::id())
            ->with(['template', 'data'])
            ->findOrFail($id);
        
        return view('front.account.resume-builder.edit', compact('resume'));
    }

    public function update(Request $request, $id)
    {
        // DEBUG: Log what we're receiving
        \Log::info('Resume Update - Raw Input:', [
            'resume_id' => $id,
            'work_experience' => $request->work_experience,
            'education' => $request->education,
            'skills' => $request->skills,
            'languages' => $request->languages,
        ]);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'job_title' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'professional_summary' => 'nullable|string|max:1000',
            'work_experience' => 'nullable|string',
            'education' => 'nullable|string',
            'skills' => 'nullable|string',
            'certifications' => 'nullable|string',
            'languages' => 'nullable|string',
            'projects' => 'nullable|string',
        ]);
        
        try {
            $resume = Resume::where('user_id', Auth::id())->findOrFail($id);
            
            $resume->update([
                'title' => $validated['title'],
            ]);

            // Handle photo upload
            $photoPath = $resume->data->personal_info['photo'] ?? null;
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($photoPath && \Storage::disk('public')->exists($photoPath)) {
                    \Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $request->file('photo')->store('resume-photos', 'public');
            }

            // Decode JSON data safely
            $workExperience = $request->work_experience ? json_decode($request->work_experience, true) : [];
            $education = $request->education ? json_decode($request->education, true) : [];
            $skills = $request->skills ? json_decode($request->skills, true) : [];
            $certifications = $request->certifications ? json_decode($request->certifications, true) : [];
            $languages = $request->languages ? json_decode($request->languages, true) : [];
            $projects = $request->projects ? json_decode($request->projects, true) : [];

            $resume->data->update([
                'personal_info' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'website' => $request->website,
                    'job_title' => $request->job_title,
                    'photo' => $photoPath,
                ],
                'professional_summary' => $request->professional_summary,
                'work_experience' => $workExperience,
                'education' => $education,
                'skills' => $skills,
                'certifications' => $certifications,
                'languages' => $languages,
                'projects' => $projects,
            ]);

            \Log::info('Resume Updated Successfully', [
                'resume_id' => $id,
                'work_exp_count' => count($workExperience),
                'education_count' => count($education),
                'skills_count' => count($skills),
            ]);

            return redirect()->route('account.resume-builder.index')
                ->with('success', 'Resume updated successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Resume update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update resume. Please try again.');
        }
    }

    public function preview($id)
    {
        $resume = Resume::where('user_id', Auth::id())
            ->with(['template', 'data'])
            ->findOrFail($id);
        
        // Use template-specific view
        $templateView = 'front.account.resume-builder.templates.' . $resume->template->slug;
        
        // Check if template view exists, fallback to default
        if (!view()->exists($templateView)) {
            $templateView = 'front.account.resume-builder.preview';
        }
        
        return view($templateView, compact('resume'));
    }

    public function download($id)
    {
        try {
            \Log::info('PDF Download Started', ['resume_id' => $id]);
            
            $resume = Resume::where('user_id', Auth::id())
                ->with(['template', 'data'])
                ->findOrFail($id);
            
            \Log::info('Resume Loaded', ['title' => $resume->title]);
            
            // Use template-specific view
            $templateView = 'front.account.resume-builder.templates.' . $resume->template->slug;
            
            // Check if template view exists, fallback to default
            if (!view()->exists($templateView)) {
                $templateView = 'front.account.resume-builder.preview';
            }
            
            \Log::info('Using template', ['view' => $templateView]);
            
            // Pass flag to template to indicate PDF generation
            $isPdfDownload = true;
            
            // Generate PDF with optimized settings
            $pdf = Pdf::loadView($templateView, compact('resume', 'isPdfDownload'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false, // Disable remote to prevent external resource loading
                    'defaultFont' => 'Arial',
                    'dpi' => 96,
                    'enable_php' => false,
                    'chroot' => public_path(), // Set root for local files
                ]);
            
            \Log::info('PDF Generated Successfully');
            
            $filename = str_replace(' ', '_', $resume->title) . '_' . date('Y-m-d') . '.pdf';
            
            \Log::info('Attempting download', ['filename' => $filename]);
            
            // Use stream instead of download for better compatibility
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Failed', [
                'resume_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }

    public function destroy($id)
    {
        $resume = Resume::where('user_id', Auth::id())->findOrFail($id);
        $resume->delete();
        
        return redirect()->route('account.resume-builder.index')
            ->with('success', 'Resume deleted successfully!');
    }
}
