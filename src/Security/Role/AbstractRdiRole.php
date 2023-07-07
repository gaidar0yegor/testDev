<?php

namespace App\Security\Role;

use App\Security\Exception\UnknownRoleException;

/**
 * Rôles personnalisés des utilisateurs,
 * dans un contexte de société ou de projet.
 */
abstract class AbstractRdiRole
{
    /**
     * Tous les rôles possible de ce contexte,
     * le rôle le plus petit en premier.
     *
     * @var string[]
     */
    protected static array $allRoles;

    /**
     * Returns whether $role is a valid role in this context.
     */
    public static function isValidRole(string $role): bool
    {
        return in_array($role, static::$allRoles, true);
    }

    /**
     * @throws UnknownRoleException When $role is unknown
     */
    public static function checkRole(string $role): void
    {
        if (!self::isValidRole($role)) {
            throw new UnknownRoleException($role, static::$allRoles);
        }
    }

    /**
     * Checks if $actualRole has the role $requiredRole.
     * Example: RoleProjet::hasRole($entity->getRole(), RoleProjet::CONTRIBUTEUR);
     *
     * @throws UnknownRoleException When $role is unknown
     */
    public static function hasRole(string $actualRole, string $requiredRole): bool
    {
        self::checkRole($requiredRole);
        self::checkRole($actualRole);

        $hasRole = false;

        foreach (static::$allRoles as $role) {
            if ($requiredRole === $role) {
                $hasRole = true;
            }

            if ($actualRole === $role) {
                return $hasRole;
            }
        }

        throw new UnknownRoleException("$actualRole or $requiredRole", static::$allRoles);
    }

    /**
     * Returns all roles that have at least $minimumRole.
     *
     * @return string[]
     */
    public static function getRoles(string $minimumRole = null): array
    {
        if (null === $minimumRole) {
            return static::$allRoles;
        }

        self::checkRole($minimumRole);

        return array_slice(static::$allRoles, array_search($minimumRole, static::$allRoles, true));
    }
}
