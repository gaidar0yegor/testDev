<?php

namespace App\Service\RdiScore;

use App\Service\RdiScore\Exception\NoElasticSearchHostException;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class ElasticSearchClientFactory
{
    public static function createClient(string $elasticSearchHost): Client
    {
        if ('' === $elasticSearchHost) {
            throw new NoElasticSearchHostException();
        }

        return ClientBuilder::create()
            ->setHosts([$elasticSearchHost])
            ->build()
        ;
    }
}
