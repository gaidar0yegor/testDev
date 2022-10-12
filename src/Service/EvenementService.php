<?php

namespace App\Service;

use App\DTO\Timesheet;
use App\Entity\EvenementParticipant;

class EvenementService
{
    /**
     * generer un array contenant les mois par EvenementParticipant avec
     * les heures journalières
     *
     * @param EvenementParticipant $evenementParticipant
     * @return array , exemple :
     *      [
     *          "2022-10" => [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,8],
     *          "2022-11" => [8,8,4,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
     *      ]
     * @throws \App\Exception\TimesheetException
     */
    public function generateHeuresMonths(EvenementParticipant $evenementParticipant): array
    {
        if (null === $evenementParticipant->getEvenement()->getProjet()) {
            return [];
        }

        $heuresParJours = Timesheet::getUserHeuresParJours($evenementParticipant->getSocieteUser());
        $workStartTime = Timesheet::getUserWorkStartTime($evenementParticipant->getSocieteUser());
        $workEndTime = Timesheet::getUserWorkEndTime($evenementParticipant->getSocieteUser());


        $evenementStartDate = $evenementParticipant->getEvenement()->getStartDate();
        $evenementEndDate = $evenementParticipant->getEvenement()->getEndDate();

        $heuresMonths = $this->initMonthsBetweenTwoDates($evenementStartDate, $evenementEndDate);

        // START:: si heure début et heure fin de l'évenement sond dans le même jour
        if ($evenementStartDate->format('Y-m-d') === $evenementEndDate->format('Y-m-d')) {
            $diffHours = self::getFloatHoursDiffDatetimes($evenementStartDate, $evenementEndDate);
            $heuresMonths[$evenementStartDate->format('Y-m')][(int)$evenementStartDate->format('d') - 1] = min($diffHours, $heuresParJours);
            return $heuresMonths;
        }
        // END:: si heure début et heure fin de l'évenement sond dans le même jour

        $loopStart = clone $evenementStartDate;
        $loopEnd = (clone $evenementEndDate)->setTime(23,59,59);
        for (
            $date = $loopStart;
            $date <= $loopEnd;
            $date->modify('+1 day')
        ) {
            if ($date->format('Y-m-d') === $evenementStartDate->format('Y-m-d')) {
                $date->setTime($workEndTime['h'], $workEndTime['m']);
                $heuresMonths[$date->format('Y-m')][(int)$date->format('d') - 1] = min(self::getFloatHoursDiffDatetimes($evenementStartDate, $date), $heuresParJours);
                continue;
            }

            if ($date->format('Y-m-d') === $evenementEndDate->format('Y-m-d')) {
                $date->setTime($workStartTime['h'], $workStartTime['m']);
                $heuresMonths[$date->format('Y-m')][(int)$date->format('d') - 1] = min(self::getFloatHoursDiffDatetimes($date, $evenementEndDate), $heuresParJours);
                continue;
            }

            $heuresMonths[$date->format('Y-m')][(int)$date->format('d') - 1] = $heuresParJours;
        }

        return $heuresMonths;
    }

    private function initMonthsBetweenTwoDates(\DateTime $dateInf, \DateTime $dateSup) : array
    {
        $start = (clone $dateInf)->modify('first day of this month');
        $end = (clone $dateSup)->modify('last day of this month');

        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end);

        $months = [];
        foreach ($period as $dt) {
            $months[$dt->format("Y-m")] = array_fill(0, $dt->format('t'), 0);
        }

        return $months;
    }

    private static function getFloatHoursDiffDatetimes(\DateTime $dateInf, \DateTime $dateSup) : float
    {
        return round(($dateSup->getTimestamp() - $dateInf->getTimestamp()) / 60 / 60, 2);
    }
}
