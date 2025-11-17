<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductVariant;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition()
    {
        return [
            'product_id' => null, // set manually
            'sku' => $this->faker->unique()->bothify('VAR-###??'),
            'price_modifier' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
