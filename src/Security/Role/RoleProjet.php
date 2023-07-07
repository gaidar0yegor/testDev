<?php

namespace App\Security\Role;

/**
 * Rôle qu'un utilisateur peut avoir sur un projet.
 */
class RoleProjet extends AbstractRdiRole
{
    /**
     * Rôle Chef de projet :
     *
     * L'utilisateur a accès à tout le projet,
     * peut le modifier, gérer les contributeurs,
     * modifier tout les faits marquants.
     *
     * /!\ Ne pas confondre avec le rôle CDP de RoleSociete.
     *
     * @var string
     */
    public const CDP = 'PROJET_CDP';

    /**
     * Rôle Contributeur :
     *
     * L'utilisateur peut ajouter des faits marquants,
     * modifier ses propres faits marquants,
     * saisir du temps passé sur le projet.
     *
     * @var string
     */
    public const CONTRIBUTEUR = 'PROJET_CONTRIBUTEUR';

    /**
     * Rôle Observateur :
     *
     * L'utilisateur a accès au projet en lecture seule.
     * Il peut voir les faits marquants, les fichiers,
     * mais ne peut rien ajouter ni modifier.
     *
     * @var string
     */
    public const OBSERVATEUR = 'PROJET_OBSERVATEUR';

    /**
     * {@inheritDoc}
     */
    protected static array $allRoles = [
        self::OBSERVATEUR,
        self::CONTRIBUTEUR,
        self::CDP,
    ];
}
