<?php

namespace App\Notification\Event;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;

/**
 * Event émit lorsqu'un utilisateur vient d'être supprimé un fait marquant
 */
class FaitMarquantRemovedEvent
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
}
