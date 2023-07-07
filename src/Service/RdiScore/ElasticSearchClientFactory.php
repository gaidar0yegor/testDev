<?php

namespace App\Service\RdiScore;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

class ElasticSearchClientFactory
{
    public static function createClient(string $elasticSearchHost, LoggerInterface $logger): Client
    {
        $builder = ClientBuilder::create();

        if ($elasticSearchHost) {
            $builder->setHosts([$elasticSearchHost]);
        } else {
            $logger->warning('You must define an Elastic search host first. Set ELASTIC_SEARCH_HOST env var.');
        }

        return $builder->build();
    }
}
