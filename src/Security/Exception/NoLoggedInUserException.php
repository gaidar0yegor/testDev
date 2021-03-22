<?php

namespace App\Security\Exception;

use LogicException;
use Throwable;

class NoLoggedInUserException extends LogicException
{
    public function __construct(string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? 'No logged in User.', 0, $previous);
    }
}
