<?php

namespace App\License\Exception;

use OverflowException;
use Throwable;

/**
 * Exception thrown when an action is not possible due license quota or expiration.
 */
class LicenseException extends OverflowException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
