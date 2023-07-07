<?php

namespace App\Notification\Event;

use App\Entity\Societe;

/**
 * Event qui concert une société,
 * ou les utilisateurs d'une même société.
 */
class SocieteNotification
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
