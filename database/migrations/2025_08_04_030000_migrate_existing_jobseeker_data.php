<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing jobseeker data from users table to jobseekers table
        $jobseekerUsers = DB::table('users')
            ->where('role', 'jobseeker')
            ->get();

        foreach ($jobseekerUsers as $user) {
            // Parse existing data from users table
            $skills = $user->skills ? json_decode($user->skills, true) : null;
            $education = $user->education ? json_decode($user->education, true) : null;
            
            // Extract names if available in user name
            $names = explode(' ', trim($user->name), 3);
            $firstName = $names[0] ?? null;
            $lastName = isset($names[1]) ? end($names) : null;
            $middleName = count($names) > 2 ? $names[1] : null;

            // Create jobseeker profile
            DB::table('jobseekers')->insert([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'phone' => $user->mobile,
                'professional_summary' => $user->bio,
                'total_experience_years' => $user->experience_years ?? 0,
                'skills' => $skills ? json_encode($skills) : null,
                'education' => $education ? json_encode($education) : null,
                'profile_photo' => $user->image,
                'profile_status' => 'incomplete', // Default status
                'profile_completion_percentage' => 0.00,
                'country' => 'Philippines', // Default
                'salary_currency' => 'PHP', // Default
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        // Similarly, migrate employer data if needed
        $employerUsers = DB::table('users')
            ->where('role', 'employer')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('employers')
                      ->whereColumn('employers.user_id', 'users.id');
            })
            ->get();

        foreach ($employerUsers as $user) {
            // Create basic employer profile if it doesn't exist
            DB::table('employers')->insert([
                'user_id' => $user->id,
                'company_name' => $user->name, // Use user name as company name temporarily
                'contact_person_name' => $user->name,
                'business_email' => $user->email,
                'business_phone' => $user->mobile,
                'status' => 'pending',
                'is_verified' => false,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        // Create admin profiles for admin users
        $adminUsers = DB::table('users')
            ->whereIn('role', ['admin', 'superadmin'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('admins')
                      ->whereColumn('admins.user_id', 'users.id');
            })
            ->get();

        foreach ($adminUsers as $user) {
            DB::table('admins')->insert([
                'user_id' => $user->id,
                'admin_level' => $user->role === 'superadmin' ? 'super_admin' : 'admin',
                'department' => 'General',
                'position' => $user->role === 'superadmin' ? 'Super Administrator' : 'Administrator',
                'can_manage_users' => true,
                'can_manage_jobs' => true,
                'can_manage_employers' => true,
                'can_view_analytics' => true,
                'can_manage_settings' => $user->role === 'superadmin',
                'can_manage_admins' => $user->role === 'superadmin',
                'status' => 'active',
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated data (optional - be careful with this in production)
        DB::table('jobseekers')->truncate();
        DB::table('employers')->where('company_name', 'LIKE', '%@%')->delete(); // Remove auto-created employer profiles
        DB::table('admins')->delete();
    }
};
