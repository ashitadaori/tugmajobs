<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->json('preliminary_answers')->nullable()->after('notes');
            $table->enum('application_step', ['basic_info', 'screening', 'documents', 'review', 'submitted'])->default('basic_info')->after('preliminary_answers');
            $table->boolean('profile_updated')->default(false)->after('application_step');
        });
    }

    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['preliminary_answers', 'application_step', 'profile_updated']);
        });
    }
};
