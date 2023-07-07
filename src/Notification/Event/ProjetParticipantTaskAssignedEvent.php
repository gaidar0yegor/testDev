<?php

namespace App\Notification\Event;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\ProjetPlanningTask;
use App\Entity\SocieteUser;

/**
 * Event émit lorsqu'un utilisateur vient de rejoindre un projet
 */
class ProjetParticipantTaskAssignedEvent
{
    private ProjetPlanningTask $projetPlanningTask;
    private ProjetParticipant $projetParticipant;

    public function __construct(
        ProjetPlanningTask $projetPlanningTask,
        ProjetParticipant $projetParticipant
    ) {
        $this->projetPlanningTask = $projetPlanningTask;
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

    public function getProjetPlanningTask(): ProjetPlanningTask
    {
        return $this->projetPlanningTask;
    }

    public function getProjet(): Projet
    {
        return $this->projetParticipant->getProjet();
    }
}
