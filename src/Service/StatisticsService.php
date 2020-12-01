<?php

namespace App\Service;

use App\Entity\Societe;
use App\Entity\User;
use App\Repository\ProjetRepository;
use App\Repository\TempsPasseRepository;
use App\Role;
use App\Service\Timesheet\TimesheetCalculator;

class StatisticsService
{
    private TempsPasseRepository $tempsPasseRepository;

    private ProjetRepository $projetRepository;

    private TimesheetCalculator $timesheetCalculator;

    public function __construct(
        TempsPasseRepository $tempsPasseRepository,
        ProjetRepository $projetRepository,
        TimesheetCalculator $timesheetCalculator
    ) {
        $this->tempsPasseRepository = $tempsPasseRepository;
        $this->projetRepository = $projetRepository;
        $this->timesheetCalculator = $timesheetCalculator;
    }

    /**
     * @return array With tuple of [string $projetAcronyme, float $heuresPassees][]
     *               Seulement pour les projets dont $user est au moins observateur.
     */
    public function calculateHeuresParProjetForUser(User $user, int $year): array
    {
        $heuresPassees = $this->calculateHeuresParProjet($user->getSociete(), $year);
        $userProjets = $user->isAdminFo()
            ? $this->projetRepository->findAllProjectsPerSociete($user->getSociete())
            : $this->projetRepository->findAllForUser($user, Role::OBSERVATEUR)
        ;

        $userProjetsHeuresPassees = [];

        foreach ($userProjets as $userProjet) {
            $userProjetsHeuresPassees[$userProjet->getAcronyme()] = $heuresPassees[$userProjet->getAcronyme()] ?? 0.0;
        }

        return $userProjetsHeuresPassees;
    }

    /**
     * @return array With tuple of [string $projetAcronyme, float $heuresPassees][]
     */
    private function calculateHeuresParProjet(Societe $societe, int $year): array
    {
        $tempsPasses = $this->tempsPasseRepository->findAllBySocieteInYear($societe, $year);
        $heuresPassees = [];

        foreach ($tempsPasses as $tempsPasse) {
            $projetIndex = $tempsPasse->getProjet()->getAcronyme();

            if (!array_key_exists($projetIndex, $heuresPassees)) {
                $heuresPassees[$projetIndex] = 0.0;
            }

            $hoursPerDay = $this->timesheetCalculator->calculateWorkedHoursPerDay($tempsPasse);
            $heuresPassees[$projetIndex] += array_sum($hoursPerDay);
        }

        return $heuresPassees;
    }
}
