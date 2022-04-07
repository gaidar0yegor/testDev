<?php

namespace App\Notification\Event;

use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;

class PlanningTaskNotCompletedNotification extends SocieteNotification
{
    private ProjetPlanningTask $projetPlanningTask;

    public function __construct(ProjetPlanningTask $projetPlanningTask)
    {
        parent::__construct($projetPlanningTask->getSociete());

        $this->projetPlanningTask = $projetPlanningTask;
    }

    public function getProjetPlanningTask(): ProjetPlanningTask
    {
        return $this->projetPlanningTask;
    }

    public function getProjet(): Projet
    {
        return $this->projetPlanningTask->getProjet();
    }
}
