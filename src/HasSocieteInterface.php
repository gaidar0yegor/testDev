<?php

namespace App;

use App\Entity\Societe;

interface HasSocieteInterface
{
    public function getSociete(): ?Societe;
}
