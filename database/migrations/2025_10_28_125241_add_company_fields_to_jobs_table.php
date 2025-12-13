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
        Schema::table('jobs', function (Blueprint $table) {
            // Add company fields for admin-posted jobs
            $table->string('company_name', 100)->nullable()->after('employer_id');
            $table->string('company_website', 255)->nullable()->after('company_name');
            
            // Rename vacancies to vacancy for consistency
            $table->renameColumn('vacancies', 'vacancy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'company_website']);
            $table->renameColumn('vacancy', 'vacancies');
        });
    }
};
