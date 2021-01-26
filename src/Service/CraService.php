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
        $days = array_fill(0, $month->format('t'), 1);

        $cra
            ->setMois($month)
            ->setJours($days)
        ;

        $this->uncheckWeekEnds($cra);
        $this->uncheckJoursFeries($cra);

        return $cra;
    }

    public function loadCraForUser(User $user, \DateTime $month): Cra
    {
        $cra = $this->craRepository->findCraByUserAndMois($user, $month);

        if (null === $cra) {
            $cra = $this->createDefaultCra($month);
            $cra->setUser($user);

            $this->uncheckJoursAvantDateEntree($cra, $user);
            $this->uncheckJoursApresDateSortie($cra, $user);
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
     * Décoche les week end.
     */
    public function uncheckWeekEnds(Cra $cra): void
    {
        $craYear = $cra->getMois()->format('Y');
        $craMonth = $cra->getMois()->format('m');
        $days = $cra->getJours();

        for ($i = 0; $i < count($cra->getJours()); ++$i) {
            $currentDate = \DateTime::createFromFormat('Y-n-j', "$craYear-$craMonth-".($i + 1));

            if (intval($currentDate->format('N')) >= 6) {
                $days[$i] = 0;
            }
        }

        $cra->setJours($days);
    }

    /**
     * Décoche les jours fériés.
     */
    public function uncheckJoursFeries(Cra $cra): void
    {
        $craYear = $cra->getMois()->format('Y');
        $craMonth = $cra->getMois()->format('m');
        $joursFeries = $this->joursFeriesCalculator->calcJoursFeries($craYear, $craMonth);
        $days = $cra->getJours();

        foreach ($joursFeries as $jourFerie) {
            $days[intval($jourFerie->format('j')) - 1] = 0;
        }

        $cra->setJours($days);
    }

    /**
     * Décoche les jours où $user n'est pas encore dans la société.
     */
    public function uncheckJoursAvantDateEntree(Cra $cra, User $user): void
    {
        if (null === $user->getDateEntree()) {
            return;
        }

        $userMonth = $this->dateMonthService->normalize($user->getDateEntree());
        $craMonth = $this->dateMonthService->normalize($cra->getMois());

        if ($craMonth > $userMonth) {
            return;
        }

        if ($craMonth < $userMonth) {
            $cra->setJours(array_fill(0, count($cra->getJours()), 0));
            return;
        }

        $jours = $cra->getJours();
        $to = intval($user->getDateEntree()->format('j')) - 1;

        for ($i = 0; $i < $to; ++$i) {
            $jours[$i] = 0;
        }

        $cra->setJours($jours);
    }

    /**
     * Décoche les jours où $user n'est plus dans la société.
     */
    public function uncheckJoursApresDateSortie(Cra $cra, User $user): void
    {
        if (null === $user->getDateSortie()) {
            return;
        }

        $userMonth = $this->dateMonthService->normalize($user->getDateSortie());
        $craMonth = $this->dateMonthService->normalize($cra->getMois());

        if ($craMonth < $userMonth) {
            return;
        }

        if ($craMonth > $userMonth) {
            $cra->setJours(array_fill(0, count($cra->getJours()), 0));
            return;
        }

        $jours = $cra->getJours();
        $from = intval($user->getDateSortie()->format('j'));

        for ($i = $from; $i < count($jours); ++$i) {
            $jours[$i] = 0;
        }

        $cra->setJours($jours);
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
