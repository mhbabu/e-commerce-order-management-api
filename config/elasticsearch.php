<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Elasticsearch connection settings. These
    | settings are used by the Elasticsearch client to connect to your
    | Elasticsearch cluster.
    |
    */

    'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),

    /*
    |--------------------------------------------------------------------------
    | Index Settings
    |--------------------------------------------------------------------------
    |
    | Define the default settings for Elasticsearch indices.
    |
    */

    'indices' => [
        'products' => [
            'name' => env('ELASTICSEARCH_PRODUCTS_INDEX', 'products'),
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
            'mappings' => [
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                    ],
                    'vendor_id' => [
                        'type' => 'integer',
                    ],
                    'name' => [
                        'type' => 'text',
                        'analyzer' => 'standard',
                    ],
                    'description' => [
                        'type' => 'text',
                        'analyzer' => 'standard',
                    ],
                    'category' => [
                        'type' => 'keyword',
                    ],
                    'sku' => [
                        'type' => 'keyword',
                    ],
                    'base_price' => [
                        'type' => 'float',
                    ],
                    'is_active' => [
                        'type' => 'boolean',
                    ],
                    'created_at' => [
                        'type' => 'date',
                        'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis',
                    ],
                    'updated_at' => [
                        'type' => 'date',
                        'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis',
                    ],
                ],
            ],
        ],
    ],
];