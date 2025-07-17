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