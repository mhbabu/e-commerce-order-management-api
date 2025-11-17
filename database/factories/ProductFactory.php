<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'vendor_id' => 1,
            'category_id' => null, // set manually
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'base_price' => $this->faker->numberBetween(100, 1000),
            'sku' => $this->faker->unique()->bothify('SKU-###??'),
            'is_active' => true,
        ];
    }
}
