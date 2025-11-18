<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductVariant;
use App\Models\Inventory;

class InventoryTableSeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::all();
        $inventoryData = [];

        foreach ($variants as $variant) {
            $inventoryData[] = [
                'product_variant_id' => $variant->id,
                'quantity'           => 50,
                'low_stock_threshold'=> 10,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        Inventory::insert($inventoryData);
    }
}
