<?php

namespace App\Service\Timesheet;

use App\Entity\ProjetParticipant;
use App\Entity\User;

interface UserContributingProjetRepositoryInterface
{
    /**
     * @return ProjetParticipant[] Tous les projets dont $user est au moins contributeur dessus
     */
    public function findProjetsContributingUser(User $user): array;
}
