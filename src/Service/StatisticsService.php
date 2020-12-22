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
     * Retourne les heures passées par projet sur les projets dont user contribue.
     *
     * @return array Array of hours contributed on projets, with projet acronym as array key.
     */
    public function calculateHeuresParProjetForUser(User $user, int $year, string $roleMinimum = Role::OBSERVATEUR): array
    {
        $heuresPassees = $this->calculateHeuresParProjet($user->getSociete(), $year);
        $userProjets = $user->isAdminFo()
            ? $this->projetRepository->findAllProjectsPerSociete($user->getSociete(), $year, $year)
            : $this->projetRepository->findAllForUserInYear($user, $roleMinimum, $year)
        ;

        $userProjetsHeuresPassees = [];

        foreach ($userProjets as $userProjet) {
            $userProjetsHeuresPassees[$userProjet->getAcronyme()] = $heuresPassees[$userProjet->getAcronyme()] ?? 0.0;
        }

        return $userProjetsHeuresPassees;
    }

    /**
     * Retourne le nombre total d'heures passées par $user sur ses projets dans l'année
     */
    public function calculateHeuresForUser(User $user, int $year): float
    {
        $tempsPasses = $this->tempsPasseRepository->findAllForUserInYear($user, $year);
        $totalHours = 0.0;

        foreach ($tempsPasses as $tempsPasse) {
            $totalHours += array_sum(
                $this->timesheetCalculator->calculateWorkedHoursPerDay($tempsPasse)
            );
        }

        return $totalHours;
    }

    /**
     * @return array With tuple of [string $projetAcronyme, float $heuresPassees][]
     */
    public function calculateHeuresParProjet(Societe $societe, int $year): array
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
