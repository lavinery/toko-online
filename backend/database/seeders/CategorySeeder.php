<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Fashion', 'parent_id' => null],
            ['name' => 'Elektronik', 'parent_id' => null],
            ['name' => 'Kesehatan & Kecantikan', 'parent_id' => null],

            // Subcategories Fashion
            ['name' => 'Pakaian Pria', 'parent_id' => 1],
            ['name' => 'Pakaian Wanita', 'parent_id' => 1],
            ['name' => 'Sepatu', 'parent_id' => 1],

            // Sub-subcategories
            ['name' => 'Kaos', 'parent_id' => 4],
            ['name' => 'Kemeja', 'parent_id' => 4],
            ['name' => 'Celana', 'parent_id' => 4],

            // Subcategories Elektronik
            ['name' => 'Smartphone', 'parent_id' => 2],
            ['name' => 'Laptop', 'parent_id' => 2],
            ['name' => 'Aksesoris', 'parent_id' => 2],
        ];

        foreach ($categories as $index => $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => 'Kategori ' . $category['name'],
                'parent_id' => $category['parent_id'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}
