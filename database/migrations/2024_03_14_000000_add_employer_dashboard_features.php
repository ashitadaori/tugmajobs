<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'views')) {
                $table->integer('views')->default(0)->after('featured');
            }
            if (!Schema::hasColumn('jobs', 'source')) {
                $table->string('source')->nullable()->after('views');
            }
            if (!Schema::hasColumn('jobs', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('source');
            }
        });

        // Add notes and interview details to job_applications table
        Schema::table('job_applications', function (Blueprint $table) {
            $table->foreignId('employer_id')->after('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('applied_date')->after('status')->nullable();
            $table->string('interview_type')->after('resume')->nullable();
            $table->timestamp('interview_date')->after('interview_type')->nullable();
            $table->text('interview_details')->after('interview_date')->nullable();
            $table->text('notes')->after('interview_details')->nullable();
            $table->string('source')->after('notes')->nullable();
        });

        // Create application_status_history table
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->onDelete('cascade');
            $table->string('old_status');
            $table->string('new_status');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Create team_members table for managing company team
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('viewer'); // viewer, recruiter, admin
            $table->json('permissions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add parent_id to users table for team hierarchy
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            $table->boolean('two_factor_enabled')->default(false)->after('remember_token');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
        });

        // Create job_views table for detailed analytics
        Schema::create('job_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['views', 'source', 'meta_data']);
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
            $table->dropColumn([
                'employer_id',
                'applied_date',
                'interview_type',
                'interview_date',
                'interview_details',
                'notes',
                'source'
            ]);
        });

        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('team_members');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['two_factor_enabled', 'two_factor_secret']);
        });

        Schema::dropIfExists('job_views');
    }
}; 