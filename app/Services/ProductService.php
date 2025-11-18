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

    public function getProductsList(array $filters)
    {
        $authUser = auth('api')->user();
        return $this->productRepository->list($filters, $authUser);
    }


    /**
     * Full creation with variants and inventory
     */
    public function createProductWithVariants(array $data, int $vendorId): Product
    {
        return DB::transaction(function () use ($data, $vendorId) {
            $variants = $data['variants'] ?? [];

            // Create product
            $product = $this->createProduct($data, $vendorId);

            // Create variants with inventory
            $this->createVariantsWithInventory($product, $variants);

            // Return product with loaded relations
            return $product->load('variants.inventory');
        });
    }

    /**
     * Create product
     */
    public function createProduct(array $data, int $vendorId): Product
    {
        return $this->productRepository->create(array_merge($data, ['vendor_id' => $vendorId]));
    }

    /**
     * Bulk insert variants with inventory
     */
    public function createVariantsWithInventory(Product $product, array $variants): void
    {
        if (empty($variants)) return;

        $variantsInsert  = [];
        $inventoryInsert = [];

        foreach ($variants as $variantData) {
            $variantsInsert[] = [
                'product_id'     => $product->id,
                'attributes'     => json_encode($variantData['attributes'] ?? []),
                'price_modifier' => $variantData['price_modifier'] ?? 0,
                'sku'            => $variantData['sku'],
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        ProductVariant::insert($variantsInsert);

        $insertedVariants = ProductVariant::where('product_id', $product->id)->get();

        foreach ($insertedVariants as $variant) {
            $originalData = collect($variants)->firstWhere('sku', $variant->sku);
            $inventoryInsert[] = [
                'product_variant_id' => $variant->id,
                'quantity'           => $originalData['quantity'] ?? 0,
                'low_stock_threshold' => $originalData['low_stock_threshold'] ?? 10,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        Inventory::insert($inventoryInsert);
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
