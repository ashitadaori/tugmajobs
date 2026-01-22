<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AuditReportController extends Controller
{
    public function index()
    {
        return view('admin.audit.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:jobseekers,employers,job_hirings,hired_jobseekers',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,pdf'
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $type = $request->type;
        $format = $request->format;

        $data = $this->fetchData($type, $startDate, $endDate);

        if ($format === 'pdf') {
            return $this->generatePdf($data, $type, $startDate, $endDate);
        } else {
            return $this->generateCsv($data, $type);
        }
    }

    private function fetchData($type, $startDate, $endDate)
    {
        switch ($type) {
            case 'jobseekers':
                return User::where('role', 'jobseeker')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('jobseeker') // Corrected from 'profile'
                    ->get()
                    ->map(function ($user) {
                        return [
                            'Name' => $user->name,
                            'Email' => $user->email,
                            'Phone' => $user->mobile_number ?? 'N/A',
                            'Status' => $user->status ?? 'Active',
                            'KYC Status' => $user->kyc_status ?? 'Unverified',
                            'Date Registered' => $user->created_at->format('Y-m-d H:i:s'),
                        ];
                    });

            case 'employers':
                return User::where('role', 'employer')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('employer') // Corrected from 'profile'
                    ->get()
                    ->map(function ($user) {
                        return [
                            'Company Name' => $user->name, // User name is often the company name for employers
                            'Email' => $user->email,
                            'Phone' => $user->mobile_number ?? 'N/A',
                            'Status' => $user->status ?? 'Active',
                            'KYC Status' => $user->kyc_status ?? 'Unverified',
                            'Date Registered' => $user->created_at->format('Y-m-d H:i:s'),
                        ];
                    });

            case 'job_hirings':
                return Job::whereBetween('created_at', [$startDate, $endDate])
                    ->with('employer')
                    ->get()
                    ->map(function ($job) {
                        return [
                            'Job Title' => $job->title,
                            'Employer' => $job->employer->name ?? 'N/A',
                            'Status' => $job->status_name ?? $job->status, // Use accessor if available
                            'Vacancies' => $job->vacancy,
                            'Date Posted' => $job->created_at->format('Y-m-d H:i:s'),
                        ];
                    });

            case 'hired_jobseekers':
                // Hired usually means application status is 'hired'
                // Or check stage = 'hired'
                return JobApplication::where(function ($query) {
                    $query->where('status', 'hired')
                        ->orWhere('stage', 'hired');
                })
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->with(['job', 'user', 'job.employer'])
                    ->get()
                    ->map(function ($app) {
                        return [
                            'Jobseeker Name' => $app->user->name ?? 'N/A',
                            'Job Title' => $app->job->title ?? 'N/A',
                            'Employer' => $app->job->employer->name ?? 'N/A',
                            'Hired Date' => $app->updated_at->format('Y-m-d H:i:s'),
                        ];
                    });

            default:
                return collect([]);
        }
    }

    private function generateCsv($data, $type)
    {
        $filename = $type . '_' . now()->format('Y_m_d_His') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = array_keys($data->first() ?? []);

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function generatePdf($data, $type, $startDate, $endDate)
    {
        $pdf = Pdf::loadView('admin.audit.pdf', [
            'data' => $data,
            'type' => ucfirst(str_replace('_', ' ', $type)),
            'dateRange' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')
        ]);

        return $pdf->download($type . '_report.pdf');
    }
}
