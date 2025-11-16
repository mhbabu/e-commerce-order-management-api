<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BulkImportProducts implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $vendorId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, int $vendorId)
    {
        $this->data = $data;
        $this->vendorId = $vendorId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $imported = 0;
        $errors = [];

        foreach ($this->data as $index => $row) {
            try {
                // Validate required fields
                if (empty($row['name']) || empty($row['sku']) || empty($row['base_price'])) {
                    $errors[] = "Row " . ($index + 1) . ": Missing required fields (name, sku, base_price)";
                    continue;
                }

                // Check if product with same SKU already exists for this vendor
                $existingProduct = \App\Models\Product::where('sku', $row['sku'])
                    ->where('vendor_id', $this->vendorId)
                    ->first();

                if ($existingProduct) {
                    $errors[] = "Row " . ($index + 1) . ": Product with SKU '{$row['sku']}' already exists";
                    continue;
                }

                // Create product
                $product = \App\Models\Product::create([
                    'name' => $row['name'],
                    'description' => $row['description'] ?? '',
                    'base_price' => (float) $row['base_price'],
                    'category' => $row['category'] ?? 'General',
                    'sku' => $row['sku'],
                    'is_active' => true,
                    'vendor_id' => $this->vendorId,
                ]);

                // Create variant (required for inventory)
                $variantAttributes = [];
                if (!empty($row['color'])) {
                    $variantAttributes['color'] = $row['color'];
                }
                if (!empty($row['size'])) {
                    $variantAttributes['size'] = $row['size'];
                }
                if (!empty($row['storage'])) {
                    $variantAttributes['storage'] = $row['storage'];
                }

                // If no attributes specified, create a default variant
                if (empty($variantAttributes)) {
                    $variantAttributes = ['default' => 'standard'];
                }

                $variant = \App\Models\ProductVariant::create([
                    'product_id' => $product->id,
                    'attributes' => $variantAttributes,
                    'price_modifier' => (float) ($row['price_modifier'] ?? 0),
                    'sku' => $row['variant_sku'] ?? $row['sku'] . '-VAR',
                ]);

                // Create inventory
                \App\Models\Inventory::create([
                    'product_variant_id' => $variant->id,
                    'quantity' => (int) ($row['quantity'] ?? 0),
                    'low_stock_threshold' => (int) ($row['low_stock_threshold'] ?? 10),
                ]);

                $imported++;

            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                Log::error("Bulk import error on row " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        // Log summary
        Log::info("Bulk import completed. Imported: {$imported}, Errors: " . count($errors));
        if (!empty($errors)) {
            Log::warning("Bulk import errors: " . implode('; ', $errors));
        }
    }
}
