<?php

namespace App;

use App\Exception\RdiException;

/**
 * Roles entre Fo users et projets
 */
class Role
{
    /**
     * @var string Constante du role "Chef de projet".
     */
    public const CDP = 'CDP';

    /**
     * @var string Constante du role "Contributeur".
     */
    public const CONTRIBUTEUR = 'CONTRIBUTEUR';

    /**
     * @var string Constante du role "Observateur".
     */
    public const OBSERVATEUR = 'OBSERVATEUR';

    /**
     * @var string[]
     */
    public static $allRoles = [
        self::OBSERVATEUR,
        self::CONTRIBUTEUR,
        self::CDP,
    ];

    /**
     * @param string $roleMinimum Si fourni, retourne tous les rôles avec $role minimum
     */
    public static function getRoles(string $roleMinimum = self::OBSERVATEUR): array
    {
        self::checkRole($roleMinimum);

        $roles = [];

        switch ($roleMinimum) {
            case self::OBSERVATEUR:
                $roles[self::OBSERVATEUR] = self::OBSERVATEUR;
                // no-break

            case self::CONTRIBUTEUR:
                $roles[self::CONTRIBUTEUR] = self::CONTRIBUTEUR;
                // no-break

            case self::CDP:
                $roles[self::CDP] = self::CDP;
                // no-break

            break;
            default:
                throw new RdiException(sprintf('Unknown role "%s".', $roleMinimum));
        }

        return $roles;
    }

    public static function checkRole(?string $role): void
    {
        if (null === $role) {
            return;
        }

        if (!in_array($role, self::$allRoles)) {
            throw new RdiException(sprintf(
                'Role expected to be one of: "%s".',
                join('", "', self::$allRoles)
            ));
        }
    }

    /**
     * Checks if $actualRole has the role $expectedRole.
     * Example: Role::hasRole($entity->getRole(), Role::CONTRIBUTEUR);
     *              returns true if $entity has role contributeur or chef de projet.
     */
    public static function hasRole(string $actualRole, string $expectedRole): bool
    {
        self::checkRole($actualRole);
        self::checkRole($expectedRole);

        return in_array($actualRole, self::getRoles($expectedRole), true);
    }
}
