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
        Schema::table('users', function (Blueprint $table) {
            $table->string('experience_level')->nullable();
            $table->unsignedInteger('salary_expectation_min')->nullable();
            $table->unsignedInteger('salary_expectation_max')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'experience_level',
                'salary_expectation_min',
                'salary_expectation_max'
            ]);
        });
    }
};
