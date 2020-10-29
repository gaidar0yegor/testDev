<?php

namespace App\Service;

use App\HasSocieteInterface;

class SocieteChecker
{
    /**
     * @return bool Si les deux entités (user, projet...) sont dans la même société.
     */
    public function isSameSociete(HasSocieteInterface $entity0, HasSocieteInterface $entity1): bool
    {
        $societe0 = $entity0->getSociete();
        $societe1 = $entity1->getSociete();

        if (null === $societe0 || null === $societe1) {
            return false;
        }

        return $societe0 === $societe1;
    }
}
