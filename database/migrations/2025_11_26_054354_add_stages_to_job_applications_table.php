<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Application stage tracking
            if (!Schema::hasColumn('job_applications', 'stage')) {
                $table->enum('stage', ['application', 'requirements', 'interview', 'hired', 'rejected'])
                      ->default('application')
                      ->after('application_step');
            }

            // Status within current stage
            if (!Schema::hasColumn('job_applications', 'stage_status')) {
                $table->enum('stage_status', ['pending', 'approved', 'rejected'])
                      ->default('pending')
                      ->after('stage');
            }

            // Submitted documents for requirements stage (JSON array of file paths)
            if (!Schema::hasColumn('job_applications', 'submitted_documents')) {
                $table->json('submitted_documents')->nullable()->after('stage_status');
            }

            // Interview scheduling fields
            if (!Schema::hasColumn('job_applications', 'interview_date')) {
                $table->date('interview_date')->nullable()->after('submitted_documents');
            }
            if (!Schema::hasColumn('job_applications', 'interview_time')) {
                $table->string('interview_time', 10)->nullable()->after('interview_date');
            }
            if (!Schema::hasColumn('job_applications', 'interview_location')) {
                $table->string('interview_location')->nullable()->after('interview_time');
            }
            if (!Schema::hasColumn('job_applications', 'interview_type')) {
                $table->enum('interview_type', ['in_person', 'video_call', 'phone'])->nullable()->after('interview_location');
            }
            if (!Schema::hasColumn('job_applications', 'interview_notes')) {
                $table->text('interview_notes')->nullable()->after('interview_type');
            }

            // Track when interview was scheduled
            if (!Schema::hasColumn('job_applications', 'interview_scheduled_at')) {
                $table->timestamp('interview_scheduled_at')->nullable()->after('interview_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'stage',
                'stage_status',
                'submitted_documents',
                'interview_date',
                'interview_time',
                'interview_location',
                'interview_type',
                'interview_notes',
                'interview_scheduled_at'
            ]);
        });
    }
};
