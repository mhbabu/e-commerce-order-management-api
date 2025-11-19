<?php

namespace App\Console\Commands;

use App\Services\Product\ProductElasticsearchService;
use Illuminate\Console\Command;

class ElasticsearchIndexProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-products {--recreate : Recreate the index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Elasticsearch index and index all products';

    protected ProductElasticsearchService $elasticsearchService;

    public function __construct(ProductElasticsearchService $elasticsearchService)
    {
        parent::__construct();
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Elasticsearch indexing process...');

        // Check if we should recreate the index
        if ($this->option('recreate')) {
            $this->info('Recreating index...');
            if ($this->elasticsearchService->indexExists()) {
                $this->elasticsearchService->deleteIndex();
                $this->info('Old index deleted.');
            }
        }

        // Create index if it doesn't exist
        if (!$this->elasticsearchService->indexExists()) {
            $this->info('Creating products index...');
            if (!$this->elasticsearchService->createIndex()) {
                $this->error('Failed to create index.');
                return Command::FAILURE;
            }
            $this->info('Index created successfully.');
        } else {
            $this->info('Index already exists.');
        }

        // Index all products
        $this->info('Indexing products...');
        $bar = $this->output->createProgressBar();
        $bar->start();

        $count = $this->elasticsearchService->indexAllProducts();

        $bar->finish();
        $this->newLine();

        $this->info("Successfully indexed {$count} products.");

        return Command::SUCCESS;
    }
}
