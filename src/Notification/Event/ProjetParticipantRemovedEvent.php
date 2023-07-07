<?php

namespace App\Notification\Event;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;

/**
 * Event émit lorsqu'un utilisateur vient d'être retiré d'un projet
 */
class ProjetParticipantRemovedEvent
{
    private ProjetParticipant $projetParticipant;

    public function __construct(
        ProjetParticipant $projetParticipant
    ) {
        $this->projetParticipant = $projetParticipant;
    }

    public function getProjetParticipant(): ProjetParticipant
    {
        return $this->projetParticipant;
    }

    public function getSocieteUser(): SocieteUser
    {
        return $this->projetParticipant->getSocieteUser();
    }

    public function getProjet(): Projet
    {
        return $this->projetParticipant->getProjet();
    }
}
