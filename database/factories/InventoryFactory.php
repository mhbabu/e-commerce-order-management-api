<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Inventory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition()
    {
        return [
            'product_variant_id' => null, // set manually
            'quantity' => $this->faker->numberBetween(10, 100),
            'low_stock_threshold' => $this->faker->numberBetween(5, 20),
        ];
    }
}
