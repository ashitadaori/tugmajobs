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
            // PosterMyWall integration fields
            $table->string('pmw_template_id')->nullable()->after('poster_type');
            $table->string('pmw_design_id')->nullable()->after('pmw_template_id');
            $table->text('pmw_preview_url')->nullable()->after('pmw_design_id');
            $table->text('pmw_download_url')->nullable()->after('pmw_preview_url');
            $table->json('pmw_customizations')->nullable()->after('pmw_download_url');
            $table->enum('source', ['local', 'postermywall'])->default('local')->after('pmw_customizations');

            // Index for faster queries
            $table->index('pmw_design_id');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posters', function (Blueprint $table) {
            $table->dropIndex(['pmw_design_id']);
            $table->dropIndex(['source']);

            $table->dropColumn([
                'pmw_template_id',
                'pmw_design_id',
                'pmw_preview_url',
                'pmw_download_url',
                'pmw_customizations',
                'source',
            ]);
        });
    }
};
