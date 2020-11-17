<?php

namespace App\Service;

use App\Exception\MonthOutOfRangeException;

/**
 * Service pour avoir des date type année/mois pour les temps saisis.
 * Avec le jour toujours au premier jour du mois.
 */
class DateMonthService
{
    /**
     * @return \DateTime from $time (defaults to now) with day set to first day (i.e 2020-10-01)
     */
    public function getCurrentMonth(string $time = 'now'): \DateTime
    {
        $month = new \DateTime($time);

        $this->normalize($month);

        return $month;
    }

    public function getMonthFromYearAndMonth(int $year = null, int $month = null): \DateTime
    {
        if ($year === null || $month === null) {
            return $this->getCurrentMonth();
        }

        if ($month < 1 || $month > 12) {
            throw new MonthOutOfRangeException($month);
        }

        $datetime = new \DateTime();

        $datetime->setDate($year, $month, 1);

        return $datetime;
    }

    public function getMonthFromYearAndMonthString(string $year = null, string $month = null): \DateTime
    {
        return $this->getMonthFromYearAndMonth(intval($year), intval($month));
    }

    /**
     * Set provided datetime day to first day.
     */
    public function normalize(\DateTime $month): void
    {
        $month->setDate($month->format('Y'), $month->format('m'), 1);
        $month->setTime(0, 0, 0);
    }

    /**
     * Retourne un nouveau datetime qui correspond au mois d'après.
     */
    public function getNextMonth(\DateTime $datetime): \DateTime
    {
        $year = (int) $datetime->format('Y');
        $month = (int) $datetime->format('m');

        $month++;

        if ($month > 12) {
            $year++;
            $month = 1;
        }

        $nextDateTime = new \DateTime();

        $nextDateTime->setDate($year, $month, 1);

        $this->normalize($nextDateTime);

        return $nextDateTime;
    }

    /**
     * Retourne un nouveau datetime qui correspond au mois d'avant.
     */
    public function getPrevMonth(\DateTime $datetime): \DateTime
    {
        $year = (int) $datetime->format('Y');
        $month = (int) $datetime->format('m');

        $month--;

        if ($month < 1) {
            $year--;
            $month = 12;
        }

        $nextDateTime = new \DateTime();

        $nextDateTime->setDate($year, $month, 1);

        $this->normalize($nextDateTime);

        return $nextDateTime;
    }
}
