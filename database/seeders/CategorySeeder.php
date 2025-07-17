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
            try {
                Category::create([
                    'name' => $category,
                    'slug' => Str::slug($category),
                    'status' => true
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // If duplicate slug, add a random suffix
                if ($e->errorInfo[1] === 1062) {
                    Category::create([
                        'name' => $category,
                        'slug' => Str::slug($category) . '-' . Str::random(5),
                        'status' => true
                    ]);
                }
            }
        }
    }
} 