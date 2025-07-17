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
            if (!Schema::hasColumn('jobs', 'experience_level')) {
                $table->enum('experience_level', ['entry', 'intermediate', 'expert'])->nullable()->after('salary_max');
            }
            if (!Schema::hasColumn('jobs', 'vacancies')) {
                $table->integer('vacancies')->default(1)->after('experience_level');
            }
            if (!Schema::hasColumn('jobs', 'location_name')) {
                $table->string('location_name')->nullable()->after('location');
            }
            if (!Schema::hasColumn('jobs', 'location_address')) {
                $table->string('location_address')->nullable()->after('location_name');
            }
            if (!Schema::hasColumn('jobs', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('location_address');
            }
            if (!Schema::hasColumn('jobs', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'experience_level',
                'vacancies',
                'location_name',
                'location_address',
                'latitude',
                'longitude'
            ]);
        });
    }
};
