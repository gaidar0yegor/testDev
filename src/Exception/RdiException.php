<?php

namespace App\Exception;

class RdiException extends \Exception
{
    public function __construct($message = '', \Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}