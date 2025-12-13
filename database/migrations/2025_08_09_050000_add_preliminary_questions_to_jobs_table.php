<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->json('preliminary_questions')->nullable()->after('meta_data');
            $table->boolean('requires_screening')->default(false)->after('preliminary_questions');
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['preliminary_questions', 'requires_screening']);
        });
    }
};
