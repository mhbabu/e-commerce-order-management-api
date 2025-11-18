<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeTableSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Color',
            'Size',
            'Material',
            'Style',
            'Brand',
            'Weight',
            'Capacity',
            'Pattern',
        ];

        foreach ($attributes as $name) {
            Attribute::create([
                'name' => $name,
                // 'is_active' => true, we set it default in database
            ]);
        }
    }
}
