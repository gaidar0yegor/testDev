<?php

namespace App\Notification\Event;

use App\Entity\Projet;
use App\Entity\SocieteUser;

/**
 * Event émit lorsqu'un utilisateur vient d'être ajouté en tant que contributeur sur un projet,
 * et qu'il peut donc maintenant ajouter des faits marquant, saisir son temps passé...
 */
class AddedAsContributorNotification
{
    private SocieteUser $societeUser;

    private Projet $projet;

    public function __construct(
        SocieteUser $societeUser,
        Projet $projet
    ) {
        $this->societeUser = $societeUser;
        $this->projet = $projet;
    }

    public function getSocieteUser(): SocieteUser
    {
        return $this->societeUser;
    }

    public function getProjet(): Projet
    {
        return $this->projet;
    }
}
