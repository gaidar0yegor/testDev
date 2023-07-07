<?php

namespace App\Notification\Event;

use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\Entity\SocieteUser;

/**
 * Event émit lorsqu'un utilisateur ajoute un fichier
 */
class FichierProjetAddedEvent
{
    private FichierProjet $fichierProjet;

    public function __construct(
        FichierProjet $fichierProjet
    ) {
        $this->fichierProjet = $fichierProjet;
    }

    public function getFichierProjet(): FichierProjet
    {
        return $this->fichierProjet;
    }

    public function getProjet(): Projet
    {
        return $this->fichierProjet->getProjet();
    }

    public function getUploadedBy(): SocieteUser
    {
        return $this->fichierProjet->getUploadedBy();
    }
}
