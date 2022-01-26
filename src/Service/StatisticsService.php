<?php

namespace App\Service;

use App\Entity\DashboardConsolide;
use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\User;
use App\Repository\CraRepository;
use App\Repository\ProjetRepository;
use App\Repository\TempsPasseRepository;
use App\Security\Role\RoleProjet;
use App\Service\Timesheet\TimesheetCalculator;

class StatisticsService
{
    private TempsPasseRepository $tempsPasseRepository;

    private ProjetRepository $projetRepository;

    private CraRepository $craRepository;

    private TimesheetCalculator $timesheetCalculator;

    public function __construct(
        TempsPasseRepository $tempsPasseRepository,
        ProjetRepository $projetRepository,
        CraRepository $craRepository,
        TimesheetCalculator $timesheetCalculator
    ) {
        $this->tempsPasseRepository = $tempsPasseRepository;
        $this->projetRepository = $projetRepository;
        $this->craRepository = $craRepository;
        $this->timesheetCalculator = $timesheetCalculator;
    }

    /**
     * Retourne les heures passées par projet sur les projets dont user contribue.
     *
     * @return array Array of hours contributed on projets, with projet acronym as array key.
     */
    public function calculateHeuresParProjetForUser(SocieteUser $societeUser, int $year, string $roleMinimum = RoleProjet::OBSERVATEUR): array
    {
        RoleProjet::checkRole($roleMinimum);

        $heuresPassees = $this->calculateHeuresParProjet($societeUser->getSociete(), $year);
        $userProjets = $societeUser->isAdminFo()
            ? $this->projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $year, $year)
            : $this->projetRepository->findAllForUserInYear($societeUser, $roleMinimum, $year)
        ;

        $userProjetsHeuresPassees = [];

        foreach ($userProjets as $userProjet) {
            $userProjetsHeuresPassees[$userProjet->getAcronyme()] = $heuresPassees[$userProjet->getAcronyme()] ?? 0.0;
        }

        return $userProjetsHeuresPassees;
    }

    /**
     * Retourne les heures passées par projet sur les projets pour un tableau de bord consolidé.
     *
     * @return array Array of hours contributed on projets.
     */
    public function calculateHeuresMultisocieteParProjetForUser(User $user, int $year, DashboardConsolide $dashboardConsolide = null, string $roleMinimum = RoleProjet::OBSERVATEUR): array
    {
        RoleProjet::checkRole($roleMinimum);

        $societeUsers = $dashboardConsolide ? $dashboardConsolide->getSocieteUsers() : $user->getSocieteUsers();

        $multisosieteProjetsHeuresPassees = [];

        foreach ($societeUsers as $societeUser) {
            $heuresPassees = $this->calculateHeuresParProjet($societeUser->getSociete(), $year);
            $userProjets = $societeUser->isAdminFo()
                ? $this->projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $year, $year)
                : $this->projetRepository->findAllForUserInYear($societeUser, $roleMinimum, $year);

            $userProjetsHeuresPassees = [];

            foreach ($userProjets as $userProjet) {
                $userProjetsHeuresPassees["{$societeUser->getSociete()->getRaisonSociale()} / {$userProjet->getAcronyme()}"] = $heuresPassees[$userProjet->getAcronyme()] ?? 0.0;
            }

            $multisosieteProjetsHeuresPassees = array_merge($multisosieteProjetsHeuresPassees, $userProjetsHeuresPassees);
        }

        return $multisosieteProjetsHeuresPassees;
    }

    /**
     * Retourne le nombre total d'heures passées par $user sur ses projets dans l'année
     */
    public function calculateHeuresForUser(SocieteUser $societeUser, int $year): float
    {
        $tempsPasses = $this->tempsPasseRepository->findAllForUserInYear($societeUser, $year);
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

    /**
     * @return int
     */
    public function calculateMonthsValidByYear(SocieteUser $societeUser, int $year): int
    {
        $nbrMonthsValid = $this->craRepository->findNumberMonthsValidByUserAndYear($societeUser, $year);

        return $nbrMonthsValid;
    }

    /**
     * @return array
     */
    public function getTempsUserParProjet(SocieteUser $societeUser, int $year, ?string $unit = 'percent'): array
    {
        $cras = $this->craRepository->findCrasByUserAndYear($societeUser, $year);

        $data = [];

        for ($i = 0; $i < 12; ++$i) {
            $data[$i] = [];
        }

        foreach ($cras as $cra) {
            $tempsPasses = [];

            foreach ($cra->getTempsPasses() as $tempsPasse) {
                switch ($unit){
                    case 'percent':
                        $tempsPasses[$tempsPasse->getProjet()->getAcronyme()] = $tempsPasse->getPourcentage();break;
                    case 'hour':
                        $tempsPasses[$tempsPasse->getProjet()->getAcronyme()] = round(array_sum($this->timesheetCalculator->calculateWorkedHoursPerDay($tempsPasse)), 1);break;
                    default:
                        $tempsPasses[$tempsPasse->getProjet()->getAcronyme()] = $tempsPasse->getPourcentage();break;
                }

            }

            $data[intval($cra->getMois()->format('m')) - 1] = $tempsPasses;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getTempsProjetParUsers(Projet $projet, int $year, ?string $unit = 'percent'): array
    {
        $tempsPasses = $this->tempsPasseRepository->findAllByProjetAndYear($projet, $year);
        $data = [];

        for ($i = 0; $i < 12; ++$i) {
            $data[$i] = [];
        }

        foreach ($tempsPasses as $tempsPasse) {
            $month = intval($tempsPasse->getCra()->getMois()->format('m')) - 1;
            $user = $tempsPasse->getCra()->getSocieteUser()->getUser()->getShortname();

            switch ($unit){
                case 'percent':
                    $data[$month][$user] = $tempsPasse->getPourcentage();break;
                case 'hour':
                    $data[$month][$user] = round(array_sum($this->timesheetCalculator->calculateWorkedHoursPerDay($tempsPasse)), 1);break;
                default:
                    $data[$month][$user] = $tempsPasse->getPourcentage();break;
            }
        }

        return $data;
    }
}
