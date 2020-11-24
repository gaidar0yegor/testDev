<?php

namespace App\Service;

use App\Entity\Cra;
use App\Entity\User;
use App\Repository\CraRepository;

class CraService
{
    private $dateMonthService;

    private $craRepository;

    private $joursFeriesCalculator;

    public function __construct(
        DateMonthService $dateMonthService,
        CraRepository $craRepository,
        JoursFeriesCalculator $joursFeriesCalculator
    ) {
        $this->dateMonthService = $dateMonthService;
        $this->craRepository = $craRepository;
        $this->joursFeriesCalculator = $joursFeriesCalculator;
    }

    /**
     * Créer un cra par défaut pour le mois donné,
     * avec les weekend et jours férié déjà décochés.
     */
    public function createDefaultCra(\DateTime $month): Cra
    {
        $cra = new Cra();

        $this->dateMonthService->normalize($month);

        $currentYear = intval($month->format('Y'));
        $currentMonth = intval($month->format('n'));

        $daysCount = intval($month->format('t'));
        $days = [];

        for ($i = 1; $i <= $daysCount; ++$i) {
            $currentDate = \DateTime::createFromFormat('Y-n-j', "$currentYear-$currentMonth-$i");

            // Jour off si weekend
            $days[] = intval($currentDate->format('N')) >= 6 ? 0 : 1;
        }

        // Met les jours férié à 0
        $joursFeries = $this->joursFeriesCalculator->calcJoursFeries($currentYear, $currentMonth);

        foreach ($joursFeries as $jourFerie) {
            $days[intval($jourFerie->format('j')) - 1] = 0;
        }

        $cra
            ->setMois($month)
            ->setJours($days)
        ;

        return $cra;
    }

    public function loadCraForUser(User $user, \DateTime $month): Cra
    {
        $cra = $this->craRepository->findCraByUserAndMois($user, $month);

        if (null === $cra) {
            $cra = $this->createDefaultCra($month);
            $cra->setUser($user);
        }

        return $cra;
    }
}
