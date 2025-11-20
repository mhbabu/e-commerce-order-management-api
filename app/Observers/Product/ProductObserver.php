<?php

namespace App\Observers\Product;

use App\Models\Product;
use App\Services\Product\ProductElasticSearchService;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    protected ProductElasticSearchService $elasticService;

    public function __construct()
    {
        // Resolve the service from Laravel container
        $this->elasticService = App::make(ProductElasticSearchService::class);
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->elasticService->indexProduct($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->elasticService->indexProduct($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        info($product);
        $this->elasticService->deleteProduct($product);
    }

    // /**
    //  * Handle the Product "restored" event.
    //  */
    // public function restored(Product $product): void
    // {
    //     $this->indexProduct($product);
    // }

    // /**
    //  * Handle the Product "force deleted" event.
    //  */
    // public function forceDeleted(Product $product): void
    // {
    //     $this->elasticService->deleteProduct($product);
    // }

    // /**
    //  * Common method to index a product
    //  */
    // protected function indexProduct(Product $product): void
    // {
    //     // Always load variants and inventory for Elasticsearch
    //     $product->load('variants.inventory');

    //     $this->elasticService->indexProduct($product);
    // }
}
