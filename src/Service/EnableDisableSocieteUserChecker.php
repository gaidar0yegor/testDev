<?php

namespace App\Service;

use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\HasSocieteInterface;
use Doctrine\ORM\EntityManagerInterface;

class EnableDisableSocieteUserChecker
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function canDisable(SocieteUser $societeUser): bool
    {
        $societeUserPeriods = $societeUser->getSocieteUserPeriods();

        if (count($societeUserPeriods) == 1 && $societeUserPeriods->first()->getDateEntry() === null && $societeUserPeriods->first()->getDateLeave() === null){
            return false;
        }

        foreach ($societeUserPeriods as $societeUserPeriod){
            if ($societeUserPeriod->getDateEntry() && $societeUserPeriod->getDateLeave() === null){
                return false;
            }
        }

        return true;
    }

    public function canEnable(SocieteUser $societeUser): bool
    {
        $societeUserPeriods = $societeUser->getSocieteUserPeriods();

        foreach ($societeUserPeriods as $societeUserPeriod){
            if ($societeUserPeriod->getDateEntry() === null){
                return false;
            }
        }

        return true;
    }
}
