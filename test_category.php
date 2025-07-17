<?php
require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

try {
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'status' => true
    ]);
    var_dump($category);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
