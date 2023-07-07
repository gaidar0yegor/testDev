<?php

namespace App\Exception;

class TimesheetException extends RdiException
{
    public function __construct($message = '', \Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
