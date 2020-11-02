<?php

namespace App\Service;

use App\Entity\Cra;
use App\Entity\User;
use App\Repository\CraRepository;

class CraService
{
    private $dateMonthService;

    private $craRepository;

    public function __construct(DateMonthService $dateMonthService, CraRepository $craRepository)
    {
        $this->dateMonthService = $dateMonthService;
        $this->craRepository = $craRepository;
    }

    /**
     * Créer un cra par défaut pour le mois donné,
     * avec les weekend et jours férié déjà décochés.
     */
    public function createDefaultCra(\DateTime $month): Cra
    {
        $cra = new Cra();

        $this->dateMonthService->normalize($month);

        $currentYear = $month->format('Y');
        $currentMonth = $month->format('m');

        $daysCount = intval($month->format('t'));
        $days = [];

        for ($i = 1; $i <= $daysCount; ++$i) {
            $currentDate = \DateTime::createFromFormat('Y-m-j', "$currentYear-$currentMonth-$i");

            $days[] = $this->isWorkingDay($currentDate) ? 1 : 0;
        }

        $cra
            ->setMois($month)
            ->setJours($days)
        ;

        return $cra;
    }

    public function loadCraForUser(User $user, \DateTime $month): Cra
    {
        $cra = $this->craRepository->findByUserAndMois($user, $month);

        if (null === $cra) {
            $cra = $this->createDefaultCra($month);
            $cra->setUser($user);
        }

        return $cra;
    }

    /**
     * Indique si $day est un jour ouvré.
     */
    public function isWorkingDay(\DateTime $day): bool
    {
        // Jour off si weekend
        if (intval($day->format('N')) >= 6) {
            return false;
        }

        return true;
    }
}
