<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Information Technology (IT) / Software Development',
            'BPO / Call Center / Customer Service',
            'Sales / Marketing',
            'Accounting / Finance',
            'Human Resources / Admin',
            'Engineering / Technical',
            'Healthcare / Medical',
            'Education / Teaching',
            'Manufacturing / Production',
            'Retail / Hospitality',
            'Media / Design / Creative',
            'Construction / Architecture',
            'Transportation / Logistics',
            'Legal / Compliance',
            'Agriculture / Farming',
            'Government / Public Service',
            'Real Estate / Property',
            'Tourism / Travel',
            'Research / Development',
            'Others'
        ];

        foreach ($categories as $category) {
            // Use firstOrCreate to prevent duplicates
            Category::firstOrCreate(
                ['name' => $category],
                [
                    'slug' => Str::slug($category),
                    'status' => true
                ]
            );
        }
    }
} 