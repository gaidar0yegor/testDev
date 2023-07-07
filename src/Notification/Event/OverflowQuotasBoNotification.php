<?php

namespace App\Notification\Event;

use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\SocieteUser;

class OverflowQuotasBoNotification
{
    private Societe $societe;
    private string $limitedElement;

    public function __construct(Societe $societe, string $limitedElement)
    {
        $this->societe = $societe;
        $this->limitedElement = $limitedElement;
    }

    public function getSociete(): Societe
    {
        return $this->societe;
    }

    public function getLimitedElement(): string
    {
        return $this->limitedElement;
    }
}
