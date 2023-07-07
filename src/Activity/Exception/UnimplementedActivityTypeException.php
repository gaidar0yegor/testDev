<?php

namespace App\Activity\Exception;

use DomainException;
use Throwable;

class UnimplementedActivityTypeException extends DomainException
{
    public function __construct(string $type, Throwable $previous = null)
    {
        parent::__construct(
            "No ActivityType implemented for the activity '$type'. Implement one or define a fallback type.",
            0,
            $previous
        );
    }
}
