<?php

namespace App\Service\Timesheet;

use App\Entity\ProjetParticipant;
use App\Entity\User;

interface UserContributingProjetRepositoryInterface
{
    /**
     * @return ProjetParticipant[]
     */
    public function findProjetsContributingUser(User $user): array;
}
