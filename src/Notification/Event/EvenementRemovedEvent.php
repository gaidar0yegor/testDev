<?php

namespace App\Notification\Event;

use App\Entity\Evenement;
use App\Entity\Projet;

/**
 * Event émit lorsqu'un utilisateur vient de supprimer un evenement
 */
class EvenementRemovedEvent
{
    private Evenement $evenement;

    public function __construct(
        Evenement $evenement
    ) {
        $this->evenement = $evenement;
    }

    public function getEvenement(): Evenement
    {
        return $this->evenement;
    }

    public function getProjet(): ?Projet
    {
        return $this->evenement->getProjet();
    }
}
