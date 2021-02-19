<?php

namespace App\RegisterSociete\DTO;

use App\Entity\Societe;
use App\Entity\User;

class Registration
{
    public ?Societe $societe = null;

    public ?User $admin = null;

    public ?string $verificationCode = null;
}
