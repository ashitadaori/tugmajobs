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
        Schema::table('posters', function (Blueprint $table) {
            // Add employer support
            $table->foreignId('employer_id')->nullable()->after('created_by')->constrained('users')->onDelete('cascade');

            // Link poster to job posting (optional)
            $table->foreignId('job_id')->nullable()->after('employer_id')->constrained('jobs')->onDelete('set null');

            // Additional customization options
            $table->string('contact_email')->nullable()->after('company_name');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('location')->nullable()->after('contact_phone');
            $table->string('salary_range')->nullable()->after('location');
            $table->string('employment_type')->nullable()->after('salary_range'); // Full-time, Part-time, etc.
            $table->date('deadline')->nullable()->after('employment_type');
            $table->string('company_logo')->nullable()->after('deadline');

            // Custom colors for more flexibility
            $table->string('primary_color')->nullable()->after('company_logo');
            $table->string('secondary_color')->nullable()->after('primary_color');

            // Track poster type (admin or employer created)
            $table->enum('poster_type', ['admin', 'employer'])->default('admin')->after('secondary_color');

            // Add index for better query performance
            $table->index(['employer_id', 'created_at']);
            $table->index(['job_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posters', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
            $table->dropForeign(['job_id']);
            $table->dropIndex(['employer_id', 'created_at']);
            $table->dropIndex(['job_id']);

            $table->dropColumn([
                'employer_id',
                'job_id',
                'contact_email',
                'contact_phone',
                'location',
                'salary_range',
                'employment_type',
                'deadline',
                'company_logo',
                'primary_color',
                'secondary_color',
                'poster_type'
            ]);
        });
    }
};
