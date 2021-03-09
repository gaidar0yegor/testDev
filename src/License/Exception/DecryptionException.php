<?php

namespace App\License\Exception;

use RuntimeException;
use Throwable;

class DecryptionException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
