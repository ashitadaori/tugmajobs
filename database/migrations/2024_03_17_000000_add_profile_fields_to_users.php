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
        Schema::table('users', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('designation');
            $table->string('location')->nullable()->after('job_title');
            $table->decimal('salary', 10, 2)->nullable()->after('location');
            $table->string('salary_type')->nullable()->after('salary');
            $table->string('qualification')->nullable()->after('salary_type');
            $table->string('language')->nullable()->after('qualification');
            $table->string('categories')->nullable()->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'job_title',
                'location',
                'salary',
                'salary_type',
                'qualification',
                'language',
                'categories'
            ]);
        });
    }
}; 