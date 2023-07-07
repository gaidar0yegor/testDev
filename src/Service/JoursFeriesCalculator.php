<?php

namespace App\Service;

use DateTime;

/**
 * Calcule les jours fériés d'une année.
 */
class JoursFeriesCalculator
{
    /**
     * @return DateTime[] Liste des jours férié de l'année $year (et du mois $month si fourni).
     */
    public function calcJoursFeries(int $year, int $month = null): array
    {
        $format = 'Y-m-d';
        $paques = $this->calcPaques($year);

        $joursFeries = [
            DateTime::createFromFormat($format, "$year-01-01"), // 1er janvier
            DateTime::createFromFormat($format, "$year-05-01"), // Fete du travail
            DateTime::createFromFormat($format, "$year-05-08"), // Victoire des allies
            DateTime::createFromFormat($format, "$year-07-14"), // Fete nationale
            DateTime::createFromFormat($format, "$year-08-15"), // Assomption
            DateTime::createFromFormat($format, "$year-11-01"), // Toussaint
            DateTime::createFromFormat($format, "$year-11-11"), // Armistice
            DateTime::createFromFormat($format, "$year-12-25"), // Noel

            (clone $paques)->modify('+01 days'), // Lundi de paques
            (clone $paques)->modify('+39 days'), // Ascension
            (clone $paques)->modify('+50 days'), // Pentecote
        ];

        sort($joursFeries);

        $joursFeries = array_map(function (DateTime $date) {
            return $date->setTime(0, 0, 0);
        }, $joursFeries);

        if (null !== $month) {
            $joursFeries = array_values(array_filter($joursFeries, function (DateTime $date) use ($month) {
                return $month === intval($date->format('n'));
            }));
        }

        return $joursFeries;
    }

    private function calcPaques(int $year): DateTime
    {
        $base = DateTime::createFromFormat('Y-m-d', "$year-03-21");
        $days = easter_days($year);

        return $base->modify("+$days days");
    }
}
