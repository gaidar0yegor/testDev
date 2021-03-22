<?php

namespace App\Security\Role;

/**
 * Rôle qu'un utilisateur peut avoir dans une société.
 */
class RoleSociete extends AbstractRdiRole
{
    /**
     * Rôle Administrateur :
     *
     * L'utilisateur peut administrer la société,
     * modifier ses infos, gérer les rôles de ses utilisateurs.
     *
     * @var string
     */
    public const ADMIN = 'SOCIETE_ADMIN';

    /**
     * Rôle Chef de projet :
     *
     * L'utilisateur peut créer des projets,
     * auquel cas il sera le chef de projet sur projet.
     *
     * @var string
     */
    public const CDP = 'SOCIETE_CDP';

    /**
     * Rôle Utilisateur :
     *
     * L'utilisateur peut accéder à la société, au tableau de bord,
     * saisir ses absences, mais n'accèdera pas aux projets tant
     * qu'il n'aura pas de RoleProjet sur un projet.
     *
     * @var string
     */
    public const USER = 'SOCIETE_USER';

    /**
     * {@inheritDoc}
     */
    protected static array $allRoles = [
        self::USER,
        self::CDP,
        self::ADMIN,
    ];
}
