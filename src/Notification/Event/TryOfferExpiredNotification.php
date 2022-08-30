<?php

namespace App\Notification\Event;

use App\Entity\Societe;

/**
 * Event émit lorsque l'offre d'éssai est expirée
 */
class TryOfferExpiredNotification
{
    private Societe $societe;

    public function __construct(Societe $societe)
    {
        $this->societe = $societe;
    }

    public function getSociete(): Societe
    {
        return $this->societe;
    }
}
