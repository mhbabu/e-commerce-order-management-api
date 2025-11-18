<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeValueTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Color'    => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple', 'Orange', 'Gray'],
            'Size'     => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'Material' => ['Cotton', 'Leather', 'Plastic', 'Metal', 'Wood', 'Glass', 'Silk'],
            'Style'    => ['Casual', 'Formal', 'Sport', 'Vintage', 'Modern'],
            'Brand'    => ['Nike', 'Adidas', 'Samsung', 'Apple', 'Sony', 'LG', 'Puma', 'Reebok'],
            'Weight'   => ['100g', '200g', '500g', '1kg', '2kg', '5kg'],
            'Capacity' => ['64GB', '128GB', '256GB', '512GB', '1TB'],
            'Pattern'  => ['Striped', 'Polka Dot', 'Plain', 'Checkered', 'Floral', 'Abstract'],
        ];

        foreach ($data as $attributeName => $values) {
            $attribute = Attribute::where('name', $attributeName)->first();

            if ($attribute) {
                foreach ($values as $value) {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value'        => $value
                    ]);
                }
            }
        }
    }
}
