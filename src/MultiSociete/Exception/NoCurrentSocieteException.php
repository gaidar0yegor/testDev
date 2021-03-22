<?php

namespace App\MultiSociete\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when trying to access current SocieteUser,
 * i.e UserContext::getSocieteUser(),
 * but user has not switched to any societe yet.
 */
class NoCurrentSocieteException extends RuntimeException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            'Trying to access SocieteUser but user has not switched to any societe yet.',
            0,
            $previous
        );
    }
}
