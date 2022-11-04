<?php

namespace App\Notification\Event;

use App\Entity\Evenement;
use App\Entity\Societe;

/**
 * Event émit pour rappler les personnes invitées aux evenements
 */
class RemindeEvenementEvent
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

    public function getSociete(): Societe
    {
        return $this->evenement->getSociete();
    }
}
