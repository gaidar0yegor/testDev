<?php

namespace App\LicenseGeneration\Exception;

use LogicException;
use Throwable;

class EncryptionKeysException extends LogicException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct(
            $message,
            0,
            $previous
        );
    }
}
