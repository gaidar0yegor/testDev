<?php

namespace App\Service\RdiScore\Exception;

use InvalidArgumentException;
use Throwable;

class NoElasticSearchHostException extends InvalidArgumentException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            'You must define an Elastic search host first. Set ELASTIC_SEARCH_HOST env var.',
            0,
            $previous
        );
    }
}
