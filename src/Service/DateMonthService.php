<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Exception\MonthOutOfRangeException;
use DateTime;
use DateTimeInterface;

/**
 * Service pour avoir des date type année/mois pour les temps saisis.
 * Avec le jour toujours au premier jour du mois.
 */
class DateMonthService
{
    /**
     * @return DateTime from $time (defaults to now) with day set to first day (i.e 2020-10-01)
     */
    public function getCurrentMonth(string $time = 'now'): DateTime
    {
        return $this->normalize(new DateTime($time));
    }

    public function getMonthFromYearAndMonth(int $year = null, int $month = null): DateTime
    {
        if ($year === null || $month === null) {
            return $this->getCurrentMonth();
        }

        if ($month < 1 || $month > 12) {
            throw new MonthOutOfRangeException($month);
        }

        $datetime = new DateTime();

        $datetime->setDate($year, $month, 1);

        return $this->normalize($datetime);
    }

    public function getMonthFromYearAndMonthString(string $year = null, string $month = null): DateTime
    {
        return $this->getMonthFromYearAndMonth(intval($year), intval($month));
    }

    /**
     * Set provided datetime day to first day.
     */
    public function normalize(DateTime $month): DateTime
    {
        $normalizedMonth = clone $month;

        $normalizedMonth->setDate(
            $normalizedMonth->format('Y'),
            $normalizedMonth->format('m'),
            1
        );

        $normalizedMonth->setTime(0, 0, 0);

        return $normalizedMonth;
    }

    public function normalizeOrNull(?DateTime $month): ?DateTime
    {
        if (null === $month) {
            return null;
        }

        return $this->normalize($month);
    }

    /**
     * Retourne un nouveau datetime qui correspond au mois d'après.
     */
    public function getNextMonth(DateTime $datetime): DateTime
    {
        $year = (int) $datetime->format('Y');
        $month = (int) $datetime->format('m');

        $month++;

        if ($month > 12) {
            $year++;
            $month = 1;
        }

        $nextDateTime = new DateTime();

        $nextDateTime->setDate($year, $month, 1);

        return $this->normalize($nextDateTime);
    }

    /**
     * Retourne un nouveau datetime qui correspond au mois d'avant.
     */
    public function getPrevMonth(DateTime $datetime): DateTime
    {
        $year = (int) $datetime->format('Y');
        $month = (int) $datetime->format('m');

        $month--;

        if ($month < 1) {
            $year--;
            $month = 12;
        }

        $nextDateTime = new DateTime();

        $nextDateTime->setDate($year, $month, 1);

        return $this->normalize($nextDateTime);
    }

    /**
     * @return bool Si le projet est actif au moins un jour dans le mois $month
     */
    public function isProjetActiveInMonth(Projet $projet, DateTimeInterface $month): bool
    {
        // Même logique que dans ProjetRepository::findAllForUser
        $month = $this->normalize($month);
        $nextMonth = $this->getNextMonth($month);

        if (null !== $projet->getDateDebut() && $nextMonth < $projet->getDateDebut()) {
            return false;
        }

        if (null !== $projet->getDateFin() && $month > $projet->getDateFin()) {
            return false;
        }

        return true;
    }

    public function isSameMonth(\DateTimeInterface $date0, \DateTimeInterface $date1): bool
    {
        return $date0->format('Y-m') === $date1->format('Y-m');
    }

    public function isUserBelongingToSocieteByDate(SocieteUser $societeUser, \DateTimeInterface $date, bool $isMonthly = true): bool
    {
        foreach ($societeUser->getSocieteUserPeriods() as $societeUserPeriod) {
            $dateEntry = $societeUserPeriod->getDateEntry() ? new \DateTime($societeUserPeriod->getDateEntry()->format($isMonthly ? 'Y-m' : 'Y-m-d')) : null;
            $dateLeave = $societeUserPeriod->getDateLeave() ? new \DateTime($societeUserPeriod->getDateLeave()->format($isMonthly ? 'Y-m' : 'Y-m-d')) : null;

            if (
                $dateEntry && $dateEntry <= $date &&
                ($dateLeave === null || ($dateLeave && $dateLeave >= $date))
            ) {
                return true;
            }
        }

        return false;
    }

    public function isSuspendedProjectByMonth(Projet $projet, \DateTimeInterface $date): bool
    {
        foreach ($projet->getProjetSuspendPeriods() as $suspendPeriod) {
            $suspendDate = $suspendPeriod->getSuspendedAt() ? new \DateTime($suspendPeriod->getSuspendedAt()->format('Y-m')) : null;
            $resumeDate = $suspendPeriod->getResumedAt() ? new \DateTime($suspendPeriod->getResumedAt()->format('Y-m')) : null;

            if (
                $suspendDate && $suspendDate < $date &&
                ($resumeDate === null || ($resumeDate && $resumeDate > $date))
            ) {
                return true;
            }
        }

        return false;
    }
}
