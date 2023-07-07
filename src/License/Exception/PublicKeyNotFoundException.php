<?php

namespace App\License\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when an action is not possible due license quota or expiration.
 */
class PublicKeyNotFoundException extends RuntimeException
{
    public function __construct(string $expectedFileLocation, ?Throwable $previous = null)
    {
        parent::__construct("Public key not found, expected to be in: $expectedFileLocation", 0, $previous);
    }
}
