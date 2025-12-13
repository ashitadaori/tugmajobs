<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResumeTemplate;

class ResumeTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Clean and professional design perfect for corporate jobs',
                'preview_image' => 'professional.png',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Modern',
                'slug' => 'modern',
                'description' => 'Contemporary design with a creative touch',
                'preview_image' => 'modern.png',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Minimalist',
                'slug' => 'minimalist',
                'description' => 'Simple and elegant design that highlights your content',
                'preview_image' => 'minimalist.png',
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($templates as $template) {
            ResumeTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
