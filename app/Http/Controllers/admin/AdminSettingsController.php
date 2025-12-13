<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name' => config('app.name'),
            'site_email' => config('mail.from.address'),
            'jobs_per_page' => config('app.jobs_per_page', 10),
            'enable_job_alerts' => config('app.enable_job_alerts', true),
            'enable_ai_features' => config('app.enable_ai_features', true),
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_email' => ['required', 'email'],
            'jobs_per_page' => ['required', 'integer', 'min:5', 'max:100'],
            'enable_job_alerts' => ['required', 'boolean'],
            'enable_ai_features' => ['required', 'boolean'],
        ]);

        // Update .env file
        $this->updateEnvironmentFile([
            'APP_NAME' => $request->site_name,
            'MAIL_FROM_ADDRESS' => $request->site_email,
            'JOBS_PER_PAGE' => $request->jobs_per_page,
            'ENABLE_JOB_ALERTS' => $request->enable_job_alerts ? 'true' : 'false',
            'ENABLE_AI_FEATURES' => $request->enable_ai_features ? 'true' : 'false',
        ]);

        // Clear config cache
        Cache::flush();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function securityLog(Request $request)
    {
        $query = \App\Models\SecurityLog::with('user')->orderBy('created_at', 'desc');

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20);

        // Get statistics
        $stats = [
            'total' => \App\Models\SecurityLog::count(),
            'today' => \App\Models\SecurityLog::whereDate('created_at', today())->count(),
            'failed' => \App\Models\SecurityLog::where('status', 'failed')->whereDate('created_at', today())->count(),
            'blocked' => \App\Models\SecurityLog::where('status', 'blocked')->whereDate('created_at', today())->count(),
        ];

        return view('admin.settings.security-log', compact('logs', 'stats'));
    }

    public function auditLog(Request $request)
    {
        $query = \App\Models\AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20);

        // Get statistics
        $stats = [
            'total' => \App\Models\AuditLog::count(),
            'today' => \App\Models\AuditLog::whereDate('created_at', today())->count(),
            'created' => \App\Models\AuditLog::where('action', 'created')->whereDate('created_at', today())->count(),
            'updated' => \App\Models\AuditLog::where('action', 'updated')->whereDate('created_at', today())->count(),
            'deleted' => \App\Models\AuditLog::where('action', 'deleted')->whereDate('created_at', today())->count(),
        ];

        // Get admin users for filter
        $admins = \App\Models\User::where('role', 'admin')->get();

        return view('admin.settings.audit-log', compact('logs', 'stats', 'admins'));
    }

    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateEnvironmentFile($data)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);

            foreach ($data as $key => $value) {
                // If the value contains spaces, wrap it in quotes
                if (str_contains($value, ' ')) {
                    $value = '"' . $value . '"';
                }

                // Replace or append the value
                if (strpos($content, $key . '=') !== false) {
                    $content = preg_replace(
                        '/^' . $key . '=.*/m',
                        $key . '=' . $value,
                        $content
                    );
                } else {
                    $content .= "\n" . $key . '=' . $value;
                }
            }

            file_put_contents($path, $content);
        }
    }
} 