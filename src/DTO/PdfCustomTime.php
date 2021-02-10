<?php

namespace App\DTO;
use DateTime;


/**
 * Classe utilisée pour le modèle du form InviteUserSurProjetType
 * qui sert à inviter un nouvel utilisateur sur un projet donné
 * à partir d'un email.
 */
class PdfCustomTime
{
    private ?DateTime $dateDebut;

    private ?DateTime $dateFin;

    public function getdateDebut(): DateTime
    {
        return $this->from;
    }

    public function setdateDebut(?DateTime $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getdateFin(): DateTime
    {
        return $this->to;
    }

    public function setdateFin(?DateTime $to): self
    {
        $this->to = $to;

        return $this;
    }
}
