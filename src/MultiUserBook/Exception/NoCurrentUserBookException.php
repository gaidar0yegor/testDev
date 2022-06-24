<?php

namespace App\MultiUserBook\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when trying to access current UserBook,
 * i.e UserContext::getUserBook(),
 * but user has not switched to any UserBook yet.
 */
class NoCurrentUserBookException extends RuntimeException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            'Trying to access UserBook but user has not switched to any UserBook yet.',
            0,
            $previous
        );
    }
}
