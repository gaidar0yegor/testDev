<?php

namespace App\Service;

use App\Entity\Cra;
use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Entity\TempsPasse;
use App\Repository\CraRepository;
use App\Repository\ProjetRepository;
use App\Security\Role\RoleProjet;

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

    public function loadCraForUser(SocieteUser $societeUser, \DateTime $month): Cra
    {
        $cra = $this->craRepository->findCraByUserAndMois($societeUser, $month);

        if (null === $cra) {
            $cra = $this->createDefaultCra($month);
            $cra->setSocieteUser($societeUser);

            $this->uncheckJoursAvantDateEntree($cra, $societeUser);
            $this->uncheckJoursApresDateSortie($cra, $societeUser);
        }

        $this->prefillTempsPasses($cra);

        return $cra;
    }

    /**
     * Initialize la liste des temps passés du Cra.
     */
    public function prefillTempsPasses(Cra $cra): void
    {
        $userProjets = $this
            ->projetRepository
            ->findAllForUser($cra->getSocieteUser(), RoleProjet::CONTRIBUTEUR, $cra->getMois())
        ;

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
     * Décoche les jours où $societeUser n'est pas encore dans la société.
     */
    public function uncheckJoursAvantDateEntree(Cra $cra, SocieteUser $societeUser): void
    {
        if (null === $societeUser->getDateEntree()) {
            return;
        }

        $userMonth = $this->dateMonthService->normalize($societeUser->getDateEntree());
        $craMonth = $this->dateMonthService->normalize($cra->getMois());

        if ($craMonth > $userMonth) {
            return;
        }

        if ($craMonth < $userMonth) {
            $cra->setJours(array_fill(0, count($cra->getJours()), 0));
            return;
        }

        $jours = $cra->getJours();
        $to = intval($societeUser->getDateEntree()->format('j')) - 1;

        for ($i = 0; $i < $to; ++$i) {
            $jours[$i] = 0;
        }

        $cra->setJours($jours);
    }

    /**
     * Décoche les jours où $societeUser n'est plus dans la société.
     */
    public function uncheckJoursApresDateSortie(Cra $cra, SocieteUser $societeUser): void
    {
        if (null === $societeUser->getDateSortie()) {
            return;
        }

        $userMonth = $this->dateMonthService->normalize($societeUser->getDateSortie());
        $craMonth = $this->dateMonthService->normalize($cra->getMois());

        if ($craMonth < $userMonth) {
            return;
        }

        if ($craMonth > $userMonth) {
            $cra->setJours(array_fill(0, count($cra->getJours()), 0));
            return;
        }

        $jours = $cra->getJours();
        $from = intval($societeUser->getDateSortie()->format('j'));

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
