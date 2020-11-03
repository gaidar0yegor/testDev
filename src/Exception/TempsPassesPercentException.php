<?php

namespace App\Exception;

class TempsPassesPercentException extends \LogicException
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
