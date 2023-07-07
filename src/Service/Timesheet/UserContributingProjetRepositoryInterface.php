<?php

namespace App\Service\Timesheet;

use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;

interface UserContributingProjetRepositoryInterface
{
    /**
     * @return ProjetParticipant[] Tous les projets dont $societeUser est au moins contributeur dessus
     */
    public function findProjetsContributingUser(SocieteUser $societeUser): array;
}
