<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductTableSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();

        $products = [
            ['name' => 'iPhone 14', 'category' => 'Electronics', 'base_price' => 999],
            ['name' => 'Samsung Galaxy S23', 'category' => 'Electronics', 'base_price' => 899],
            ['name' => 'MacBook Pro 16"', 'category' => 'Electronics', 'base_price' => 2399],
            ['name' => 'Dell XPS 15', 'category' => 'Electronics', 'base_price' => 1799],
            ['name' => 'Sony WH-1000XM5 Headphones', 'category' => 'Electronics', 'base_price' => 399],
            ['name' => 'Nike Air Max 270', 'category' => 'Fashion', 'base_price' => 150],
            ['name' => 'Adidas Ultraboost', 'category' => 'Fashion', 'base_price' => 180],
            ['name' => 'Levi’s 501 Jeans', 'category' => 'Fashion', 'base_price' => 60],
            ['name' => 'Ray-Ban Wayfarer Sunglasses', 'category' => 'Fashion', 'base_price' => 120],
            ['name' => 'Casio G-Shock Watch', 'category' => 'Fashion', 'base_price' => 99],
            ['name' => 'Instant Pot Duo 7-in-1', 'category' => 'Home & Kitchen', 'base_price' => 120],
            ['name' => 'Dyson V11 Vacuum Cleaner', 'category' => 'Home & Kitchen', 'base_price' => 599],
            ['name' => 'Philips Air Fryer', 'category' => 'Home & Kitchen', 'base_price' => 150],
            ['name' => 'Keurig K-Elite Coffee Maker', 'category' => 'Home & Kitchen', 'base_price' => 170],
            ['name' => 'Lodge Cast Iron Skillet', 'category' => 'Home & Kitchen', 'base_price' => 35],
            ['name' => 'Canon EOS R5 Camera', 'category' => 'Electronics', 'base_price' => 3899],
            ['name' => 'GoPro Hero 11', 'category' => 'Electronics', 'base_price' => 499],
            ['name' => 'Apple Watch Series 9', 'category' => 'Electronics', 'base_price' => 399],
            ['name' => 'Bose SoundLink Revolve', 'category' => 'Electronics', 'base_price' => 199],
            ['name' => 'Samsung QLED 4K TV', 'category' => 'Electronics', 'base_price' => 999],
            ['name' => 'Patagonia Down Jacket', 'category' => 'Fashion', 'base_price' => 299],
            ['name' => 'North Face Backpack', 'category' => 'Fashion', 'base_price' => 120],
            ['name' => 'Adidas Running Shorts', 'category' => 'Fashion', 'base_price' => 35],
            ['name' => 'Nike Sports Socks', 'category' => 'Fashion', 'base_price' => 12],
            ['name' => 'Samsung Galaxy Tab S8', 'category' => 'Electronics', 'base_price' => 699],
            ['name' => 'HP Envy Laptop', 'category' => 'Electronics', 'base_price' => 1299],
            ['name' => 'Apple iPad Pro', 'category' => 'Electronics', 'base_price' => 1099],
            ['name' => 'Fitbit Charge 6', 'category' => 'Electronics', 'base_price' => 149],
            ['name' => 'Sony PlayStation 5', 'category' => 'Electronics', 'base_price' => 499],
            ['name' => 'Xbox Series X', 'category' => 'Electronics', 'base_price' => 499],
            ['name' => 'Samsung Galaxy Buds 2', 'category' => 'Electronics', 'base_price' => 149],
            ['name' => 'Google Pixel 8', 'category' => 'Electronics', 'base_price' => 799],
            ['name' => 'Amazon Echo Dot', 'category' => 'Electronics', 'base_price' => 49],
            ['name' => 'Kindle Paperwhite', 'category' => 'Electronics', 'base_price' => 139],
            ['name' => 'Oculus Quest 2', 'category' => 'Electronics', 'base_price' => 299],
            ['name' => 'Logitech MX Master 3 Mouse', 'category' => 'Electronics', 'base_price' => 99],
            ['name' => 'Razer BlackWidow Keyboard', 'category' => 'Electronics', 'base_price' => 139],
            ['name' => 'Sony Bravia 55" TV', 'category' => 'Electronics', 'base_price' => 799],
            ['name' => 'JBL Flip 6 Speaker', 'category' => 'Electronics', 'base_price' => 129],
            ['name' => 'Apple AirPods Pro', 'category' => 'Electronics', 'base_price' => 249],
            ['name' => 'Samsung Galaxy Watch 6', 'category' => 'Electronics', 'base_price' => 349],
            ['name' => 'Dyson Airwrap', 'category' => 'Beauty & Personal Care', 'base_price' => 549],
            ['name' => 'Revlon Hair Dryer', 'category' => 'Beauty & Personal Care', 'base_price' => 59],
            ['name' => 'Philips Electric Shaver', 'category' => 'Beauty & Personal Care', 'base_price' => 79],
            ['name' => 'L’Oreal Hair Color', 'category' => 'Beauty & Personal Care', 'base_price' => 15],
            ['name' => 'Nike Yoga Pants', 'category' => 'Fashion', 'base_price' => 60],
            ['name' => 'Adidas Hoodie', 'category' => 'Fashion', 'base_price' => 80],
            ['name' => 'Fitbit Versa 4', 'category' => 'Electronics', 'base_price' => 229],
            ['name' => 'Canon Pixma Printer', 'category' => 'Electronics', 'base_price' => 129],
            ['name' => 'HP LaserJet Pro', 'category' => 'Electronics', 'base_price' => 249],
            ['name' => 'Sony Alpha A7 III', 'category' => 'Electronics', 'base_price' => 1999],
            ['name' => 'Nikon Z6 II', 'category' => 'Electronics', 'base_price' => 1599],
            ['name' => 'Apple Mac Mini', 'category' => 'Electronics', 'base_price' => 699],
            ['name' => 'Samsung Smart Fridge', 'category' => 'Home & Kitchen', 'base_price' => 1499],
            ['name' => 'KitchenAid Stand Mixer', 'category' => 'Home & Kitchen', 'base_price' => 379],
            ['name' => 'Instant Pot Duo Nova', 'category' => 'Home & Kitchen', 'base_price' => 129],
            ['name' => 'Breville Espresso Machine', 'category' => 'Home & Kitchen', 'base_price' => 599],
        ];

        $productsData = [];

        foreach ($products as $index => $item) {
            $vendor = $vendors[$index % $vendors->count()]; // rotate vendors

            $productsData[] = [
                'name'        => $item['name'],
                'description' => "High-quality product: {$item['name']}",
                'base_price'  => $item['base_price'],
                'vendor_id'   => $vendor->id,
                'category'    => $item['category'],
                'sku'         => 'PROD' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'is_active'   => true,
            ];
        }

        Product::insert($productsData);
    }
}
