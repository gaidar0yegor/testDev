<?php

namespace App\Service;

use App\Entity\Societe;
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

    /**
     * @param HasSocieteInterface[] $entities
     * @param Societe|null $societe
     *
     * @return bool Si toutes les $entities sont dans la même société.
     *      Si $societe est défini, vérifie que toutes mes $entities sont dans cette $societe.
     */
    public function allSameSociete(iterable $entities, Societe $societe = null): bool
    {
        foreach ($entities as $entity) {
            if (null === $societe) {
                $societe = $entity->getSociete();
                continue;
            }

            if ($entity->getSociete() !== $societe) {
                return false;
            }
        }

        return true;
    }
}
