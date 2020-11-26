<?php

namespace App\Service;

use App\Entity\Cra;
use App\Entity\Projet;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Repository\CraRepository;
use App\Repository\ProjetRepository;
use App\Role;

class CraService
{
    private $dateMonthService;

    private $craRepository;

    private $projetRepository;

    private $joursFeriesCalculator;

    public function __construct(
        DateMonthService $dateMonthService,
        CraRepository $craRepository,
        ProjetRepository $projetRepository,
        JoursFeriesCalculator $joursFeriesCalculator
    ) {
        $this->dateMonthService = $dateMonthService;
        $this->craRepository = $craRepository;
        $this->projetRepository = $projetRepository;
        $this->joursFeriesCalculator = $joursFeriesCalculator;
    }

    /**
     * Créer un cra par défaut pour le mois donné,
     * avec les weekend et jours férié déjà décochés.
     */
    public function createDefaultCra(\DateTime $month): Cra
    {
        $cra = new Cra();

        $month = $this->dateMonthService->normalize($month);

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

        $this->prefillTempsPasses($cra);

        return $cra;
    }

    /**
     * Initialize la liste des temps passés du Cra.
     */
    public function prefillTempsPasses(Cra $cra): void
    {
        $userProjets = $this->projetRepository->findAllForUser($cra->getUser(), Role::CONTRIBUTEUR, $cra->getMois());

        foreach ($userProjets as $userProjet) {
            if ($this->craContainsProjet($cra, $userProjet)) {
                continue;
            }

            $tempsPasse = new TempsPasse();

            $tempsPasse
                ->setProjet($userProjet)
                ->setPourcentage(0)
            ;

            $cra->addTempsPass($tempsPasse);
        }
    }

    /**
     * @param TempsPasse[] $tempsPasses Liste de temps passés à verifier si un est lié au $projet.
     * @param Projet $projet
     *
     * @return bool Si Un des temps passé correspond au projet.
     */
    private function craContainsProjet(Cra $cra, Projet $projet): bool
    {
        foreach ($cra->getTempsPasses() as $tempsPasse) {
            if ($tempsPasse->getProjet() === $projet) {
                return true;
            }
        }

        return false;
    }
}
