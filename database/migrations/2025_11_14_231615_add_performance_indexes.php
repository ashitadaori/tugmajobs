<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add performance indexes to frequently queried fields
     *
     * @return void
     */
    public function up()
    {
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('deleted_at');
            $table->index(['role', 'is_active'], 'users_role_active_index');
            $table->index('kyc_status');
        });

        // Add indexes to employers table
        Schema::table('employers', function (Blueprint $table) {
            $table->index('city');
            $table->index('status');
            $table->index('is_verified');
            $table->index('is_featured');
            $table->index(['status', 'is_verified', 'is_featured'], 'employers_status_composite_index');
        });

        // Add indexes to jobseekers table
        Schema::table('jobseekers', function (Blueprint $table) {
            $table->index('profile_status');
            $table->index('is_featured');
            $table->index('city');
            $table->index(['profile_status', 'is_featured'], 'jobseekers_status_featured_index');
        });

        echo "✓ Added performance indexes to users, employers, and jobseekers tables\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex('users_role_active_index');
            $table->dropIndex(['kyc_status']);
        });

        // Drop indexes from employers table
        Schema::table('employers', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex('employers_status_composite_index');
        });

        // Drop indexes from jobseekers table
        Schema::table('jobseekers', function (Blueprint $table) {
            $table->dropIndex(['profile_status']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['city']);
            $table->dropIndex('jobseekers_status_featured_index');
        });
    }
};
