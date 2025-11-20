<?php

namespace App\Services\Common;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticService
{
    protected $client;

    public function __construct()
    {
        $hosts = [
            env('ELASTIC_HOST', 'http://127.0.0.1:9200'), // load all the .env data from config is more best practice
        ];

        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setBasicAuthentication(
                env('ELASTIC_USERNAME'),
                env('ELASTIC_PASSWORD')
            )
            ->build();
    }

    /**
     * Get Elasticsearch client
     */
    public function getClient()
    {
        return $this->client;
    }
}
