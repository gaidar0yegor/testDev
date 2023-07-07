<?php

namespace App\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when the user has already the labo on invitation.
 *
 * This exception should be catched by a listener,
 * and display an explicit 404 error page.
 */
class InvitationTokenAlreadyHasLaboException extends RuntimeException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);
    }
}
