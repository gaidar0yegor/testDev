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
     * @param string $roleMinimum Si fourni, retourne tous les rôles avec $role minimum
     */
    public static function getRoles(string $roleMinimum = self::OBSERVATEUR): array
    {
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
}
