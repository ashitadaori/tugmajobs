<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Redirect users to the appropriate profile page based on their role
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isEmployer()) {
            return redirect()->route("employer.profile.edit");
        } elseif ($user->isJobSeeker()) {
            return redirect()->route("account.myProfile");
        } elseif ($user->isAdmin()) {
            return redirect()->route("admin.profile.edit");
        } else {
            // Default fallback
            return redirect()->route("account.myProfile");
        }
    }
}