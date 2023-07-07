<?php

namespace App\Service\Timesheet;

use App\Entity\Cra;
use App\Entity\SocieteUser;

interface UserMonthCraRepositoryInterface
{
    public function findCraByUserAndMois(SocieteUser $societeUser, \DateTimeInterface $mois): ?Cra;
}
