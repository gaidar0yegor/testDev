<?php

namespace App\Notification\Event;

use App\Entity\Societe;
use App\License\DTO\License;

/**
 * Event émit lorsque l'offre d'une société est changée depuis le back office
 */
class SocieteProductModifiedNotification
{
    private Societe $societe;
    private License $oldLicense;
    private License $newLicense;

    public function __construct(Societe $societe, License $oldLicense, License $newLicense)
    {
        $this->societe = $societe;
        $this->oldLicense = $oldLicense;
        $this->newLicense = $newLicense;
    }

    public function getSociete(): Societe
    {
        return $this->societe;
    }

    public function getOldLicense(): ?License
    {
        return $this->oldLicense;
    }

    public function getNewLicense(): License
    {
        return $this->newLicense;
    }
}
