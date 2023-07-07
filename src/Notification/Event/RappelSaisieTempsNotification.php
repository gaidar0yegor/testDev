<?php

namespace App\Notification\Event;

use App\Entity\Societe;
use DateTime;
use DateTimeInterface;

/**
 * Quand cet event est émit,
 * c'est qu'il faut envoyer un rappel de saisie des temps
 * aux utilisateurs de la société $societe,
 * pour le mois $month.
 */
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
