<?php

namespace App\Listener;

use App\Entity\SocieteUserPeriod;
use Doctrine\ORM\Event\LifecycleEventArgs;

class SocieteUserPeriodEntityListener
{
    public function postUpdate(SocieteUserPeriod $societeUserPeriod, LifecycleEventArgs $args): void
    {
        $societeUser = $societeUserPeriod->getSocieteUser();
        $lastPeriod = $societeUser->getSocieteUserPeriods()->last();

        if ($lastPeriod->getDateEntry() && $lastPeriod->getDateLeave()){
            $societeUserPeriod = new SocieteUserPeriod();
            $societeUserPeriod->setSocieteUser($societeUser);

            $args->getEntityManager()->persist($societeUserPeriod);
            $args->getEntityManager()->persist($societeUser);
            $args->getEntityManager()->flush();
        }
    }
}
