<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantTableSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $variantsData = [];

        foreach ($products as $product) {
            for ($i = 1; $i <= 2; $i++) { // 2 variants per product
                $variantsData[] = [
                    'product_id'     => $product->id,
                    'price_modifier' => ($i - 1) * 20, // 0, 20
                    'sku'            => $product->sku . "-V$i",
                    'is_active'      => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
        }

        ProductVariant::insert($variantsData);
    }
}
