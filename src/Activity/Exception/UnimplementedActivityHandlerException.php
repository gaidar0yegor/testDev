<?php

namespace App\Activity\Exception;

use DomainException;
use Throwable;

class UnimplementedActivityHandlerException extends DomainException
{
    public function __construct(string $type, Throwable $previous = null)
    {
        parent::__construct(
            "No ActivityHandler implemented for the activity '$type'. Implement one or define a fallback handler.",
            0,
            $previous
        );
    }
}
