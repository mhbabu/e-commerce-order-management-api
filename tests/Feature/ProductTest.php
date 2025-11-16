<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_product()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($vendor);

        $data = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'base_price' => 29.99,
            'category' => 'Test',
            'sku' => 'TEST001',
            'variants' => [
                [
                    'attributes' => ['size' => 'M'],
                    'price_modifier' => 0,
                    'sku' => 'TEST001-M',
                    'quantity' => 50,
                    'low_stock_threshold' => 5,
                ],
            ],
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/products', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure(['name', 'variants']);
    }

    public function test_customer_cannot_create_product()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($customer);

        $data = [
            'name' => 'Test Product',
            'base_price' => 29.99,
            'sku' => 'TEST001',
            'variants' => [],
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/products', $data);

        $response->assertStatus(403);
    }
}
