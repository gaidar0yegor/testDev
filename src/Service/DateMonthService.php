<?php

namespace App\Service;

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

    /**
     * Set provided datetime day to first day.
     */
    public function normalize(\DateTime $month): void
    {
        $month->setDate($month->format('Y'), $month->format('m'), 1);
        $month->setTime(0, 0, 0);
    }
}
