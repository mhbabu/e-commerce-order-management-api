<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Repositories\ProductRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * List products with filters (pagination handled in repository)
     */
    public function getProductsList(array $filters)
    {
        $authUser = auth('api')->user();
        return $this->productRepository->list($filters, $authUser);
    }

    /**
     * Create product with variants and inventory
     */
    public function createProductWithVariants(array $data, int $vendorId): Product
    {
        return DB::transaction(function () use ($data, $vendorId) {
            $variants = $data['variants'] ?? [];
            unset($data['variants']);

            // Create product
            $product = $this->productRepository->create(array_merge($data, ['vendor_id' => $vendorId]));

            // Create variants + inventory
            $this->createOrUpdateVariantsWithInventory($product, $variants);

            return $product->load('variants.inventory');
        });
    }

    /**
     * Update product with variants and inventory
     */
    public function updateProductWithVariants(int $id, array $data)
    {
        $product = $this->productRepository->find($id);
        if (!$product) return null;  // Not found

        $currentUser = auth('api')->user();

        // Vendor can only update their own products
        if ($currentUser->role === 'vendor' && $product->vendor_id !== $currentUser->id) {
            return 'unauthorized'; // Permission denied
        }

        return DB::transaction(function () use ($product, $data) {
            $variants = $data['variants'] ?? [];
            unset($data['variants']);

            // Update product
            $this->productRepository->update($product->id, $data);

            // Update or create variants + inventory
            $this->createOrUpdateVariantsWithInventory($product, $variants);

            return $product->load('variants.inventory');
        });
    }

    /**
     * Show single product
     */
    public function findProduct(int $id): ?Product
    {
        return $this->productRepository->find($id)?->load('variants.inventory');
    }

    /**
     * Delete product
     */
    public function deleteProduct(int $id)
    {
        $product = $this->productRepository->find($id);
        if (!$product) return null; // Not found

        $currentUser = auth('api')->user();

        // Vendor can only delete their own products
        if ($currentUser->role === 'vendor' && $product->vendor_id !== $currentUser->id) {
            return 'unauthorized';
        }

        return DB::transaction(function () use ($id) {

            // Get variant IDs first
            $variantIds = ProductVariant::where('product_id', $id)->pluck('id');

            // Delete inventory first
            Inventory::whereIn('product_variant_id', $variantIds)->delete();

            // Delete variants
            ProductVariant::whereIn('id', $variantIds)->delete();

            // Delete product
            return $this->productRepository->delete($id);
        });
    }


    /**
     * Bulk create or update variants with inventory
     */
    protected function createOrUpdateVariantsWithInventory(Product $product, array $variants): void
    {
        foreach ($variants as $variantData) {
            $variant = ProductVariant::updateOrCreate(
                ['sku' => $variantData['sku']],
                [
                    'product_id'     => $product->id,
                    'attributes'     => $variantData['attributes'] ?? [],
                    'price_modifier' => $variantData['price_modifier'] ?? 0,
                    'is_active'      => $variantData['is_active'] ?? true,
                ]
            );

            Inventory::updateOrCreate(
                ['product_variant_id' => $variant->id],
                [
                    'quantity'            => $variantData['quantity'] ?? 0,
                    'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 10,
                ]
            );
        }
    }


    public function importProductsFromCsv(UploadedFile $file, int $vendorId = 1): array
    {
        return $this->productRepository->bulkImport($file, $vendorId);
    }
}
