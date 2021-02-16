<?php

namespace App\DTO;
use DateTime;


/**
 * Classe utilisée pour le modèle du form InviteUserSurProjetType
 * qui sert à inviter un nouvel utilisateur sur un projet donné
 * à partir d'un email.
 */
class ProjetExportParameters
{
    private ?DateTime $dateDebut;

    private ?DateTime $dateFin;

    public function getdateDebut(): ?DateTime
    {
        return $this->dateDebut;
    }

    public function setdateDebut(?DateTime $from): self
    {
        $this->dateDebut = $from;

        return $this;
    }

    public function getdateFin(): ?DateTime
    {
        return $this->dateFin;
    }

    public function setdateFin(?DateTime $to): self
    {
        $this->dateFin = $to;

        return $this;
    }
}
