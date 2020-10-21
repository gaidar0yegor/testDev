<?php

namespace App\Exception;

class MonthOutOfRangeException extends \OutOfBoundsException
{
    public function __construct(int $month, \Throwable $previous = null)
    {
        $message = sprintf('Expected month between 1 and 12, "%s" given.', $month);

        parent::__construct($message, $previous);
    }
}
