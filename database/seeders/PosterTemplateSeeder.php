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
            [
                'name' => 'Gradient Purple',
                'slug' => 'gradient-purple',
                'description' => 'Modern gradient purple design with bold typography',
                'preview_image' => 'poster-templates/gradient-purple-preview.png',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Minimalist Green',
                'slug' => 'minimalist-green',
                'description' => 'Clean minimalist design with green accents',
                'preview_image' => 'poster-templates/minimalist-green-preview.png',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'Bold Red',
                'slug' => 'bold-red',
                'description' => 'Eye-catching red design with white content card and checkmark bullets',
                'preview_image' => 'poster-templates/bold-red-preview.png',
                'is_active' => true,
                'display_order' => 6,
            ],
            [
                'name' => 'Elegant Navy',
                'slug' => 'elegant-navy',
                'description' => 'Sophisticated dark navy design with gold accents for professional appeal',
                'preview_image' => 'poster-templates/elegant-navy-preview.png',
                'is_active' => true,
                'display_order' => 7,
            ],
            [
                'name' => 'Sunset Orange',
                'slug' => 'sunset-orange',
                'description' => 'Warm gradient orange design with numbered requirements and modern styling',
                'preview_image' => 'poster-templates/sunset-orange-preview.png',
                'is_active' => true,
                'display_order' => 8,
            ],
            [
                'name' => 'Tech Dark',
                'slug' => 'tech-dark',
                'description' => 'Modern tech-inspired dark theme with code-style elements and blue accents',
                'preview_image' => 'poster-templates/tech-dark-preview.png',
                'is_active' => true,
                'display_order' => 9,
            ],
            [
                'name' => 'Fresh Teal',
                'slug' => 'fresh-teal',
                'description' => 'Light and fresh teal design with organic shapes and clean typography',
                'preview_image' => 'poster-templates/fresh-teal-preview.png',
                'is_active' => true,
                'display_order' => 10,
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
