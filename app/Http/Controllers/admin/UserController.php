<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request){
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by KYC status
        if ($request->filled('kyc_status')) {
            if ($request->kyc_status !== 'all') {
                $query->where('kyc_status', $request->kyc_status);
            }
        }

        // Filter by registration date
        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $start = Carbon::parse($dates[0])->startOfDay();
                $end = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->with(['employer', 'jobseeker'])
                      ->paginate(10)
                      ->withQueryString();

        // Get counts for filters
        $counts = [
            'total' => User::count(),
            'verified' => User::where('kyc_status', 'verified')->count(),
            'unverified' => User::where('kyc_status', '!=', 'verified')->count(),
            'employers' => User::where('role', 'employer')->count(),
            'jobseekers' => User::where('role', 'jobseeker')->count(),
            'admins' => User::whereIn('role', ['admin', 'superadmin'])->count(),
            'kyc_verified' => User::where('kyc_status', 'verified')->count(),
            'kyc_pending' => User::where('kyc_status', 'in_progress')->count(),
            'kyc_rejected' => User::where('kyc_status', 'rejected')->count(),
            'kyc_not_started' => User::where('kyc_status', 'not_started')->count(),
        ];

        return view('admin.users.list',[
            'users' => $users,
            'counts' => $counts,
            'filters' => $request->all()
        ]);
    }

    public function show($id){
        $user = User::with(['employer', 'jobseeker'])->findOrFail($id);
        
        // Get user's jobs if employer
        $jobs = [];
        if ($user->role === 'employer') {
            $jobs = Job::where('user_id', $user->id)
                ->with(['jobType', 'category'])
                ->withCount('applications')
                ->orderBy('created_at', 'DESC')
                ->get();
        }
        
        // Get user's applications if jobseeker
        $applications = [];
        if ($user->role === 'jobseeker') {
            $applications = JobApplication::where('user_id', $user->id)
                ->with(['job.employer', 'job.jobType'])
                ->orderBy('created_at', 'DESC')
                ->get();
        }
        
        return view('admin.users.show', [
            'user' => $user,
            'jobs' => $jobs,
            'applications' => $applications,
        ]);
    }

    public function edit($id){
        $user = User::with(['employer', 'jobseeker'])->findOrFail($id);
        return view('admin.users.edit',[
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id){
        $data = [
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,'.$id.',id',
        ];

        $validator = Validator::make($request->all(),$data);

        if($validator->passes()){
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->designation = $request->designation;
            $user->mobile = $request->mobile;
            $user->save();

            Session()->flash('success','User information updated successfully.');
            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request){
        $user = User::find($request->id);

        if($user == null){
            Session()->flash('error','User not found');
            return response()->json([
                'status' => false,
            ]);
        }

        $user->delete();
        Session()->flash('success','User deleted successfully');
        return response()->json([
            'status' => true,
        ]);
    }

    public function suspend(Request $request, User $user) {
        $user->is_active = false;
        $user->save();

        Session()->flash('success', 'User suspended successfully');
        return response()->json(['status' => true]);
    }

    public function unsuspend(Request $request, User $user) {
        $user->is_active = true;
        $user->save();

        Session()->flash('success', 'User unsuspended successfully');
        return response()->json(['status' => true]);
    }

    public function forceKycReverification(Request $request, User $user) {
        // Reset KYC status to require reverification
        $user->kyc_status = 'not_started';
        $user->kyc_session_id = null;
        $user->kyc_completed_at = null;
        $user->kyc_verified_at = null;
        $user->save();

        Session()->flash('success', 'User KYC reverification requested');
        return response()->json(['status' => true]);
    }

    public function export(Request $request) {
        $query = User::query();
        
        // Apply filters same as index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->get();

        $csvData = [];
        $csvData[] = [
            'ID', 'Name', 'Email', 'Role', 'Status', 'Registered Date',
            'Phone', 'Designation', 'Profile Type', 'Last Login'
        ];

        foreach ($users as $user) {
            $csvData[] = [
                $user->id,
                $user->name,
                $user->email,
                ucfirst($user->role),
                $user->email_verified_at ? 'Verified' : 'Unverified',
                $user->created_at->format('Y-m-d H:i:s'),
                $user->mobile ?? 'N/A',
                $user->designation ?? 'N/A',
                $user->employer ? 'Employer' : ($user->jobseeker ? 'Job Seeker' : 'N/A'),
                $user->last_login_at ?? 'Never'
            ];
        }

        $filename = 'users-export-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function bulkAction(Request $request) {
        $request->validate([
            'action' => 'required|in:delete,suspend,unsuspend,force-kyc',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $count = 0;

        foreach ($users as $user) {
            switch ($request->action) {
                case 'delete':
                    $user->delete();
                    break;
                case 'suspend':
                    $user->is_active = false;
                    $user->save();
                    break;
                case 'unsuspend':
                    $user->is_active = true;
                    $user->save();
                    break;
                case 'force-kyc':
                    // Reset KYC status to require reverification
                    $user->kyc_status = 'not_started';
                    $user->kyc_session_id = null;
                    $user->kyc_completed_at = null;
                    $user->kyc_verified_at = null;
                    $user->save();
                    break;
            }
            $count++;
        }

        $action = str_replace('-', ' ', $request->action);
        Session()->flash('success', "Successfully {$action}ed {$count} users");
        return response()->json(['status' => true]);
    }
}
