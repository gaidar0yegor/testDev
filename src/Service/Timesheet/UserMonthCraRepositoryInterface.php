<?php

namespace App\Service\Timesheet;

use App\Entity\Cra;
use App\Entity\User;

interface UserMonthCraRepositoryInterface
{
    public function findCraByUserAndMois(User $user, \DateTimeInterface $mois): ?Cra;
}
