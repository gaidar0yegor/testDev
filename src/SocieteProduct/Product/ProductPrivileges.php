<?php


namespace App\SocieteProduct\Product;


class ProductPrivileges
{

    // ------- STANDARD :: Standard Product Privileges -------

    /**
     * Taille de la description des faits marquants
     */
    public const FAIT_MARQUANT_DESCRIPTION_SIZE = 'FAIT_MARQUANT_DESCRIPTION_SIZE';

    /**
     * Date des faits marquants
     */
    public const FAIT_MARQUANT_DATE = 'FAIT_MARQUANT_DATE';

    /**
     * Envoie des faits marquants par mail
     */
    public const FAIT_MARQUANT_SEND_MAIL = 'FAIT_MARQUANT_SEND_MAIL';

    /**
     * Relance SMS du suivi du temps
     */
    public const SMS_NOTIFICATION_SAISIE_TEMPS = 'SMS_NOTIFICATION_SAISIE_TEMPS';

    /**
     * Droit de visibilité des fichiers
     */
    public const FICHIER_PROJET_ACCESSES = 'FICHIER_PROJET_ACCESSES';

    // ------- PREMIUM :: Premium Product Privileges -------

    /**
     * Tableaux de bord multi-société
     */
    public const MULTI_SOCIETE_DASHBOARD = 'MULTI_SOCIETE_DASHBOARD';

    /**
     * Liens hiérarchiques
     */
    public const SOCIETE_HIERARCHICAL_SUPERIOR = 'SOCIETE_HIERARCHICAL_SUPERIOR';
}