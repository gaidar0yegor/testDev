<?php

namespace App\Service;

use App\DTO\Timesheet;
use App\Entity\EvenementParticipant;

class EvenementService
{
    /**
     * Calculer le nombre d'heure de travaille par evenement
     */
    public function calculeWorkHoursPerEventParticipant(EvenementParticipant $evenementParticipant): float
    {
        if (null === $evenementParticipant->getEvenement()->getProjet()) {
            return 0;
        }

        $evenementStartDate = $evenementParticipant->getEvenement()->getStartDate();
        $evenementEndDate = $evenementParticipant->getEvenement()->getEndDate();

        $heuresParJours = Timesheet::getUserHeuresParJours($evenementParticipant->getSocieteUser());
        $workStartTime = Timesheet::getUserWorkStartTime($evenementParticipant->getSocieteUser());
        $workEndTime = Timesheet::getUserWorkEndTime($evenementParticipant->getSocieteUser());

        $diffHours = self::getFloatHoursDiffDatetimes($evenementStartDate, $evenementEndDate);

        if ($evenementStartDate->format('Y-m-d') === $evenementEndDate->format('Y-m-d')) {
            return min($diffHours, $heuresParJours);
        }

        $workHours = 0;
        for (
            $date = clone $evenementStartDate;
            $date <= (clone $evenementEndDate)->setTime(23,59,59);
            $date->modify('+1 day')
        ) {
            if ($date->format('Y-m-d') === $evenementStartDate->format('Y-m-d')) {
                $date->setTime($workEndTime['h'], $workEndTime['m']);
                $workHours += min(self::getFloatHoursDiffDatetimes($evenementStartDate, $date), $heuresParJours);
                continue;
            }

            if ($date->format('Y-m-d') === $evenementEndDate->format('Y-m-d')) {
                $date->setTime($workStartTime['h'], $workStartTime['m']);
                $workHours += min(self::getFloatHoursDiffDatetimes($date, $evenementEndDate), $heuresParJours);
                continue;
            }

            $workHours += $heuresParJours;
        }

        return $workHours;
    }

    private static function getFloatHoursDiffDatetimes(\DateTime $dateInf, \DateTime $dateSup) : float
    {
        return ($dateSup->getTimestamp() - $dateInf->getTimestamp()) / 60 / 60;
    }
}
