<?php

namespace App\Notification\Event;

use App\Entity\Societe;

/**
 * Event émit lorsqu'une société est désactivée depuis le back office
 */
class SocieteDisabledNotification
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
