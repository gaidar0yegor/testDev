<?php

namespace App\Notification\Event;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;

/**
 * Event émit lorsqu'un utilisateur vient de restaurer un fait marquant
 */
class FaitMarquantRestoredEvent
{
    private FaitMarquant $faitMarquant;

    public function __construct(
        FaitMarquant $faitMarquant
    ) {
        $this->faitMarquant = $faitMarquant;
    }

    public function getFaitMarquant(): FaitMarquant
    {
        return $this->faitMarquant;
    }

    public function getProjet(): Projet
    {
        return $this->faitMarquant->getProjet();
    }

    public function getCreatedBy(): SocieteUser
    {
        return $this->faitMarquant->getCreatedBy();
    }

    public function getRemovedBy(): SocieteUser
    {
        return $this->faitMarquant->getTrashedBy();
    }
}
