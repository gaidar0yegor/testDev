<?php

namespace App\RegisterSociete\DTO;

use App\Entity\LabApp\Labo;
use App\Entity\Societe;
use App\Entity\User;

class Registration
{
    public ?Labo $labo = null;

    public ?Societe $societe = null;

    public ?User $admin = null;

    public ?string $verificationCode = null;
}
