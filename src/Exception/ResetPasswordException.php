<?php

namespace App\Exception;

class ResetPasswordException extends RdiException
{
    public function __construct($message = '', \Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
