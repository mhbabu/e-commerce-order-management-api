<?php

namespace App\Jobs\Product;

use App\Models\Product;
use App\Services\Product\ProductElasticSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProductReIndexingForElasticSearchingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ProductElasticSearchService $elastic;
    protected ?int $vendorId;

    public function __construct(?int $vendorId = null)
    {
        $this->vendorId = $vendorId;
    }

    public function handle(ProductElasticSearchService $elastic)
    {
        $this->elastic = $elastic;

        Log::info('Product reindexing started.', ['vendor_id' => $this->vendorId]);

        $query = Product::with('variants.inventory')->latest();

        if ($this->vendorId) {
            $query->where('vendor_id', $this->vendorId);
        }

        if ($query->exists()) { // Only proceed if there are products
            $query->chunk(500, function ($products) {
                $count = $products->count();
                if ($count > 0) {
                    $this->elastic->bulkIndex($products);
                    Log::info("Indexed chunk of {$count} products", ['vendor_id' => $this->vendorId]);
                }
            });
        } else {
            Log::info('No products found to reindex.', ['vendor_id' => $this->vendorId]);
        }

        Log::info('Product reindexing completed.', ['vendor_id' => $this->vendorId]);
    }
}
