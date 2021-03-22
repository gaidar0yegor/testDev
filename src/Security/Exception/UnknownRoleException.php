<?php

namespace App\Security\Exception;

use DomainException;
use Throwable;

class UnknownRoleException extends DomainException
{
    public function __construct(string $unexpectedRole, array $allRoles, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'Role expected to be one of: "%s", got "%s".',
                join('", "', $allRoles),
                $unexpectedRole
            ),
            0,
            $previous
        );
    }
}
