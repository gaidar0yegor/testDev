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
        $emptyPeriod = $args->getEntityManager()->getRepository(SocieteUserPeriod::class)->findBy([
            'societeUser' => $societeUser->getId(),
            'dateEntry' => null,
            'dateLeave' => null,
        ]);

        if (count($emptyPeriod) === 0 && $lastPeriod->getDateEntry() && $lastPeriod->getDateLeave()){
            $societeUserPeriod = new SocieteUserPeriod();
            $societeUserPeriod->setSocieteUser($societeUser);

            $args->getEntityManager()->persist($societeUserPeriod);
            $args->getEntityManager()->persist($societeUser);
            $args->getEntityManager()->flush();
        }

        if (count($emptyPeriod) > 1){
            for ($i = 0; $i < count($emptyPeriod) - 1; $i++ ){
                $args->getEntityManager()->remove($emptyPeriod[$i]);
            }
            $args->getEntityManager()->flush();
        }
    }
}
