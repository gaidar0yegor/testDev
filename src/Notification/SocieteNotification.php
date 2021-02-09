<?php

namespace App\Notification;

use App\Entity\Societe;

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
