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
            $table->decimal('salary_min', 12, 2)->nullable()->after('benefits');
            $table->decimal('salary_max', 12, 2)->nullable()->after('salary_min');
            // Drop the old salary_range column if it exists
            if (Schema::hasColumn('jobs', 'salary_range')) {
                $table->dropColumn('salary_range');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('salary_range')->nullable()->after('benefits');
            $table->dropColumn(['salary_min', 'salary_max']);
        });
    }
};
