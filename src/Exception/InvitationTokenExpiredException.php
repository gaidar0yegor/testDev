<?php

namespace App\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when an invitation token does not exists,
 * because either expired, incorrect, or already used.
 *
 * This exception should be catched by a listener,
 * and display an explicit 404 error page.
 */
class InvitationTokenExpiredException extends RuntimeException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);
    }
}
