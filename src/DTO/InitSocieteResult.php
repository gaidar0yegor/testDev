<?php

namespace App\DTO;

use App\Entity\Societe;
use App\Entity\User;

class InitSocieteResult
{
    private Societe $societe;

    private User $admin;

    public function __construct(Societe $societe, User $admin)
    {
        $this->societe = $societe;
        $this->admin = $admin;
    }

    public function getSociete(): Societe
    {
        return $this->societe;
    }

    public function getAdmin(): User
    {
        return $this->admin;
    }
}
