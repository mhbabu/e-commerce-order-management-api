<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create vendor user
        $vendor = User::factory()->create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'role' => 'vendor',
        ]);

        // Create customer user
        $customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);

        // Create sample product
        $product = \App\Models\Product::create([
            'name' => 'Sample T-Shirt',
            'description' => 'A comfortable cotton t-shirt',
            'base_price' => 19.99,
            'vendor_id' => $vendor->id,
            'category' => 'Clothing',
            'sku' => 'TSHIRT001',
        ]);

        // Create variants
        $variant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'attributes' => ['size' => 'M', 'color' => 'Blue'],
            'price_modifier' => 0,
            'sku' => 'TSHIRT001-M-BLUE',
        ]);

        // Create inventory
        \App\Models\Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 100,
            'low_stock_threshold' => 10,
            'last_updated' => now(),
        ]);

        // Create sample order
        $order = \App\Models\Order::create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 19.99,
            'shipping_address' => '123 Main St, City, State 12345',
            'billing_address' => '123 Main St, City, State 12345',
            'order_number' => 'ORD-123456',
        ]);

        // Create order item
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 19.99,
        ]);
    }
}
