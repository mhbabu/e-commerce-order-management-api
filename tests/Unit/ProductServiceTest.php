<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product()
    {
        $repository = new ProductRepository(new Product());
        $service = new ProductService($repository);

        $userId = 1; // Assume user exists
        $data = [
            'name' => 'Test Product',
            'base_price' => 10.00,
            'sku' => 'TEST123',
        ];

        $product = $service->createProduct($data, $userId);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
    }
}
