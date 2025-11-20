<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Product\ProductReIndexingForElasticSearchingJob;
use Illuminate\Support\Facades\Log;

class ProductReIndexingForElasticSearching extends Command
{
    protected $signature = 'products:reindex';
    protected $description = 'Reindex all products in Elasticsearch';

    public function handle()
    {
        Log::info('Starting Product reindexing command...');
        $this->info('Dispatching Product reindexing job...');

        // Dispatch the queued job to reindex all products
        ProductReIndexingForElasticSearchingJob::dispatch();

        $this->info('Product reindexing job dispatched successfully.');
        Log::info('Product reindexing job dispatched successfully.');
    }
}
