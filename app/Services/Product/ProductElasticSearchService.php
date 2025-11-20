<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Services\Common\ElasticService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProductElasticSearchService
{
    protected ElasticService $elastic;
    protected string $index;

    public function __construct(ElasticService $elastic)
    {
        $this->elastic = $elastic;
        $this->index = env('ELASTIC_INDEX'); // use config is most standard
    }

    /**
     * Index or update a single product in Elasticsearch
     */
    public function indexProduct(Product $product): void
    {
        $product->loadMissing('variants.inventory');

        $variants = $product->variants->map(function ($variant) {
            return [
                'sku' => $variant->sku,
                'attributes' => $variant->attributes,
                'price_modifier' => $variant->price_modifier,
                'quantity' => $variant->inventory->quantity ?? 0,
                'low_stock_threshold' => $variant->inventory->low_stock_threshold ?? 0,
            ];
        })->toArray();

        try {
            $this->elastic->getClient()->index([
                'index' => $this->index,
                'id' => $product->id,
                'body' => [
                    'name' => $product->name,
                    'description' => $product->description,
                    'base_price' => $product->base_price,
                    'category' => $product->category,
                    'vendor_id' => $product->vendor_id,
                    'variants' => $variants,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to index product in Elasticsearch", [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a product from Elasticsearch
     */
    public function deleteProduct(Product $product): void
    {
        try {
            $this->elastic->getClient()->delete([
                'index' => $this->index,
                'id' => $product->id,
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to delete product from Elasticsearch (may not exist)", [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bulk index products (chunked to avoid memory issues)
     *
     * @param Collection|array $products
     */
    public function bulkIndex(Collection|array $products): void
    {
        $products = collect($products);

        $products->chunk(500)->each(function ($chunk) {
            $body = [];

            foreach ($chunk as $product) {
                info($product);
                if ($product instanceof Product) {
                    $product->loadMissing('variants.inventory');

                    $variants = $product->variants->map(function ($variant) {
                        return [
                            'sku' => $variant->sku,
                            'attributes' => $variant->attributes,
                            'price_modifier' => $variant->price_modifier,
                            'quantity' => $variant->inventory->quantity ?? 0,
                            'low_stock_threshold' => $variant->inventory->low_stock_threshold ?? 0,
                        ];
                    })->toArray();

                    $body[] = ['index' => ['_index' => $this->index, '_id' => $product->id]];
                    $body[] = [
                        'name' => $product->name,
                        'description' => $product->description,
                        'base_price' => $product->base_price,
                        'category' => $product->category,
                        'vendor_id' => $product->vendor_id,
                        'variants' => $variants,
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at,
                    ];
                } else {
                    // If array format passed
                    $body[] = ['index' => ['_index' => $this->index, '_id' => $product['id']]];
                    $body[] = $product;
                }
            }

            if (!empty($body)) {
                try {
                    $this->elastic->getClient()->bulk(['body' => $body]);
                } catch (\Exception $e) {
                    Log::error("Failed bulk index chunk in Elasticsearch", ['error' => $e->getMessage()]);
                }
            }
        });
    }

    /**
     * Search products in Elasticsearch
     *
     * @param string $query
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search(string $query = '', array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'must' => [],
                        'filter' => [],
                    ]
                ]
            ]
        ];

        // Add full-text search
        if ($query !== '') {
            $params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query'  => $query,
                    'fields' => [
                        'name^3',
                        'description',
                        'category',
                        'variants.sku',
                        'variants.attributes.color',
                        'variants.attributes.storage'
                    ]
                ]
            ];
        }

        // Add filters
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $params['body']['query']['bool']['filter'][] = ['term' => [$field => $value]];
            }
        }

        try {
            $results = $this->elastic->getClient()->search($params);
        } catch (\Exception $e) {
            Log::error("Elasticsearch search failed", ['error' => $e->getMessage()]);
            return ['total' => 0, 'data' => []];
        }

        $hits = $results['hits']['hits'] ?? [];

        return [
            'total' => $results['hits']['total']['value'] ?? 0,
            'data'  => array_map(fn($hit) => array_merge(['id' => $hit['_id']], $hit['_source']), $hits),
        ];
    }
}
