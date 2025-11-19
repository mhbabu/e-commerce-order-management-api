<?php

namespace App\Services\Product;

use App\Models\Product;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

class ProductElasticsearchService
{
    protected $client;
    protected $indexName;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'))
            ->build();

        $this->indexName = config('elasticsearch.indices.products.name');
    }

    /**
     * Create the products index with mapping
     */
    public function createIndex(): bool
    {
        $params = [
            'index' => $this->indexName,
            'body' => [
                'settings' => config('elasticsearch.indices.products.settings'),
                'mappings' => config('elasticsearch.indices.products.mappings'),
            ],
        ];

        try {
            $response = $this->client->indices()->create($params);
            Log::info('Elasticsearch products index created', ['response' => $response]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create Elasticsearch products index', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete the products index
     */
    public function deleteIndex(): bool
    {
        $params = ['index' => $this->indexName];

        try {
            $response = $this->client->indices()->delete($params);
            Log::info('Elasticsearch products index deleted', ['response' => $response]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete Elasticsearch products index', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Index a single product
     */
    public function indexProduct(Product $product): bool
    {
        $params = [
            'index' => $this->indexName,
            'id' => $product->id,
            'body' => $this->formatProductForIndex($product),
        ];

        try {
            $response = $this->client->index($params);
            Log::info('Product indexed in Elasticsearch', ['product_id' => $product->id, 'response' => $response]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to index product in Elasticsearch', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove a product from index
     */
    public function removeProduct(int $productId): bool
    {
        $params = [
            'index' => $this->indexName,
            'id' => $productId,
        ];

        try {
            $response = $this->client->delete($params);
            Log::info('Product removed from Elasticsearch', ['product_id' => $productId, 'response' => $response]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to remove product from Elasticsearch', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Search products using Elasticsearch
     */
    public function searchProducts(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $query = [
            'bool' => [
                'must' => [],
                'filter' => [],
            ],
        ];

        // Add search query if provided
        if (!empty($filters['search'])) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query' => $filters['search'],
                    'fields' => ['name^3', 'description^2', 'category', 'sku'],
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        // Add category filter if provided
        if (!empty($filters['category'])) {
            $query['bool']['filter'][] = [
                'term' => ['category' => $filters['category']],
            ];
        }

        // Add vendor filter if provided
        if (!empty($filters['vendor_id'])) {
            $query['bool']['filter'][] = [
                'term' => ['vendor_id' => $filters['vendor_id']],
            ];
        }

        // Add active filter
        $query['bool']['filter'][] = [
            'term' => ['is_active' => true],
        ];

        $params = [
            'index' => $this->indexName,
            'body' => [
                'query' => $query,
                'sort' => [
                    ['_score' => ['order' => 'desc']],
                    ['created_at' => ['order' => 'desc']],
                ],
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
            ],
        ];

        try {
            $response = $this->client->search($params);

            $productIds = array_column($response['hits']['hits'], '_id');
            $total = $response['hits']['total']['value'] ?? 0;

            return [
                'product_ids' => $productIds,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
            ];
        } catch (\Exception $e) {
            Log::error('Elasticsearch search failed', ['error' => $e->getMessage(), 'filters' => $filters]);
            return [
                'product_ids' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => 1,
            ];
        }
    }

    /**
     * Index all existing products
     */
    public function indexAllProducts(): int
    {
        $products = Product::with('variants.inventory')->get();
        $count = 0;

        foreach ($products as $product) {
            if ($this->indexProduct($product)) {
                $count++;
            }
        }

        Log::info('Bulk indexing completed', ['total_indexed' => $count]);
        return $count;
    }

    /**
     * Format product data for indexing
     */
    protected function formatProductForIndex(Product $product): array
    {
        return [
            'id' => $product->id,
            'vendor_id' => $product->vendor_id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category,
            'sku' => $product->sku,
            'base_price' => (float) $product->base_price,
            'is_active' => $product->is_active,
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString(),
        ];
    }

    /**
     * Check if index exists
     */
    public function indexExists(): bool
    {
        $params = ['index' => $this->indexName];

        try {
            $response = $this->client->indices()->exists($params);
            return isset($response['acknowledged']) ? $response['acknowledged'] : true;
        } catch (\Exception $e) {
            Log::error('Failed to check if index exists', ['error' => $e->getMessage()]);
            return false;
        }
    }
}