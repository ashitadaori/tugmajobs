<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First ensure the icon column exists
        if (!Schema::hasColumn('categories', 'icon')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('icon')->nullable()->after('status');
            });
        }

        // Get the icon map
        $iconMap = [
            'engineering' => '🔧',
            'design' => '🎨',
            'marketing' => '📢',
            'sales' => '💼',
            'human-resources' => '👥',
            'information-technology' => '💻',
            'software-development' => '⌨️',
            'web-development' => '🌐',
            'mobile-development' => '📱',
            'data-science' => '📊',
            'artificial-intelligence' => '🤖',
            'cloud-computing' => '☁️',
            'cybersecurity' => '🔒',
            'devops' => '⚙️',
            'ui-ux-design' => '🎨',
            'digital-marketing' => '📱',
            'content-writing' => '✍️',
            'project-management' => '📋',
            'business-analysis' => '📈',
            'customer-service' => '🤝',
            'finance' => '💰',
            'healthcare' => '⚕️',
            'education' => '📚'
        ];

        // Update all categories with their corresponding icons
        foreach (Category::all() as $category) {
            $icon = $iconMap[$category->slug] ?? '📋';
            $category->update(['icon' => $icon]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('categories', 'icon')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('icon');
            });
        }
    }
}; 