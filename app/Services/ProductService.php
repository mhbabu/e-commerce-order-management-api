<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function createProduct(array $data, int $vendorId): Product
    {
        return DB::transaction(function () use ($data, $vendorId) {
            $product = $this->productRepository->create(array_merge($data, ['vendor_id' => $vendorId]));
            return $product;
        });
    }

    public function createVariant(int $productId, array $variantData, array $inventoryData): ProductVariant
    {
        return DB::transaction(function () use ($productId, $variantData, $inventoryData) {
            $variant = ProductVariant::create(array_merge($variantData, ['product_id' => $productId]));
            Inventory::create(array_merge($inventoryData, ['product_variant_id' => $variant->id]));
            return $variant;
        });
    }

    public function getProductsByVendor(int $vendorId): Collection
    {
        return $this->productRepository->findByVendor($vendorId);
    }

    public function searchProducts(string $query): Collection
    {
        return $this->productRepository->search($query);
    }

    public function updateProduct(int $id, array $data): bool
    {
        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct(int $id): bool
    {
        return $this->productRepository->delete($id);
    }

    public function find(int $id)
    {
        return $this->productRepository->find($id);
    }
}