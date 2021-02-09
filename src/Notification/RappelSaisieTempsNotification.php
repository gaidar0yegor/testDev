<?php

namespace App\Notification;

use App\Entity\Societe;
use DateTime;
use DateTimeInterface;

class RappelSaisieTempsNotification extends SocieteNotification
{
    private DateTimeInterface $month;

    public function __construct(Societe $societe, DateTimeInterface $month = null)
    {
        parent::__construct($societe);

        $this->month = $month ?? new DateTime();
    }

    public function getMonth(): DateTimeInterface
    {
        return $this->month;
    }
}
