<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Electronics',
            'Fashion',
            'Home & Kitchen',
            'Beauty & Personal Care',
            'Sports & Outdoors',
            'Toys & Games',
            'Automotive',
            'Books',
            'Health & Wellness',
            'Office Supplies',
        ];

        foreach ($categories as $name) {
            Category::create([
                'name'      => $name
                // 'is_active' => true, // we set it defalut true 
            ]);
        }
    }
}
