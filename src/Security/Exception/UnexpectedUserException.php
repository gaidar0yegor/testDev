<?php

namespace App\Security\Exception;

use App\Entity\User;
use RuntimeException;

class UnexpectedUserException extends RuntimeException
{
    public function __construct($userInstance, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'Expected an user instance of "%s", got an instance of "%s".',
                User::class,
                null === $userInstance ? 'NULL' : get_class($userInstance)
            ),
            $previous
        );
    }
}
