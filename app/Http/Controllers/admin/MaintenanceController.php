<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSetting;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $jobseekerMaintenance = MaintenanceSetting::where('key', 'jobseeker_maintenance')->first();
        $employerMaintenance = MaintenanceSetting::where('key', 'employer_maintenance')->first();

        return view('admin.maintenance.index', compact('jobseekerMaintenance', 'employerMaintenance'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jobseeker_active' => 'boolean',
            'jobseeker_message' => 'required|string|max:500',
            'employer_active' => 'boolean',
            'employer_message' => 'required|string|max:500',
        ]);

        // Update jobseeker maintenance
        MaintenanceSetting::updateOrCreate(
            ['key' => 'jobseeker_maintenance'],
            [
                'is_active' => $request->boolean('jobseeker_active'),
                'message' => $request->jobseeker_message,
            ]
        );

        // Update employer maintenance
        MaintenanceSetting::updateOrCreate(
            ['key' => 'employer_maintenance'],
            [
                'is_active' => $request->boolean('employer_active'),
                'message' => $request->employer_message,
            ]
        );

        // Clear cache
        MaintenanceSetting::clearCache();

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance settings updated successfully!');
    }
}
