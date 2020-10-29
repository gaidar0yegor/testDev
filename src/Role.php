<?php

namespace App;

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

    public static function getRoles(): array
    {
        return [
            self::CDP => self::CDP,
            self::CONTRIBUTEUR => self::CONTRIBUTEUR,
            self::OBSERVATEUR => self::OBSERVATEUR,
        ];
    }
}
