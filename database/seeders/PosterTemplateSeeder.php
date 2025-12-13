<?php

namespace Database\Seeders;

use App\Models\PosterTemplate;
use Illuminate\Database\Seeder;

class PosterTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Blue Megaphone',
                'slug' => 'blue-megaphone',
                'description' => 'Bold blue design with "WE ARE HIRING" text and megaphone illustration',
                'preview_image' => 'poster-templates/blue-megaphone-preview.png',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Yellow Attention',
                'slug' => 'yellow-attention',
                'description' => 'Vibrant yellow design with "ATTENTION PLEASE!" text and 3D megaphone',
                'preview_image' => 'poster-templates/yellow-attention-preview.png',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Modern Corporate',
                'slug' => 'modern-corporate',
                'description' => 'Professional black and yellow design with laptop background',
                'preview_image' => 'poster-templates/modern-corporate-preview.png',
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($templates as $template) {
            PosterTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
