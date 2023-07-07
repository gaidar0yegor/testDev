<?php

namespace App\Security\Role;

/**
 * Rôle qu'un utilisateur peut avoir dans un cahier de labo / labo.
 */
class RoleLabo extends AbstractRdiRole
{
    /**
     * Rôle Administrateur
     *
     * @var string
     */
    public const ADMIN = 'LABO_ADMIN';

    /**
     * Rôle Chercheur senior
     *
     * @var string
     */
    public const SENIOR = 'LABO_SENIOR';

    /**
     * Rôle Utilisateur
     *
     * @var string
     */
    public const USER = 'LABO_USER';

    /**
     * {@inheritDoc}
     */
    protected static array $allRoles = [
        self::USER,
        self::SENIOR,
        self::ADMIN,
    ];
}
