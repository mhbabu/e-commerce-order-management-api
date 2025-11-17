<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductVariantAttributeValue;

class ProductVariantAttributeValueFactory extends Factory
{
    protected $model = ProductVariantAttributeValue::class;

    public function definition()
    {
        return [
            'product_variant_id' => null, // set manually
            'attribute_id' => null,      // set manually
            'attribute_value_id' => null // set manually
        ];
    }
}
