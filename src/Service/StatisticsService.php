<?php

namespace App\Service;

use App\DTO\Timesheet;
use App\Entity\DashboardConsolide;
use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Repository\CraRepository;
use App\Repository\ProjetRepository;
use App\Repository\SocieteUserRepository;
use App\Repository\TempsPasseRepository;
use App\Security\Role\RoleProjet;
use App\Service\Timesheet\TimesheetCalculator;

class StatisticsService
{
    private TempsPasseRepository $tempsPasseRepository;

    private ProjetRepository $projetRepository;

    private SocieteUserRepository $societeUserRepository;

    private CraRepository $craRepository;

    private CraService $craService;

    private TimesheetCalculator $timesheetCalculator;

    public function __construct(
        TempsPasseRepository $tempsPasseRepository,
        ProjetRepository $projetRepository,
        SocieteUserRepository $societeUserRepository,
        CraRepository $craRepository,
        CraService $craService,
        TimesheetCalculator $timesheetCalculator
    ) {
        $this->tempsPasseRepository = $tempsPasseRepository;
        $this->projetRepository = $projetRepository;
        $this->societeUserRepository = $societeUserRepository;
        $this->craRepository = $craRepository;
        $this->craService = $craService;
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

        if ($societeUser->isAdminFo()){
            $userProjets = $this->projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $year, $year);
        } elseif ($societeUser->isSuperiorFo()) {
            $userProjets = $this->projetRepository->findAllForUsers($this->societeUserRepository->findTeamMembers($societeUser), $year, $year);
        } else {
            $userProjets = $this->projetRepository->findAllForUserInYear($societeUser, $roleMinimum, $year);
        }


        $userProjetsHeuresPassees = [];
        $codeColors = [];

        foreach ($userProjets as $userProjet) {
            $userProjetsHeuresPassees[$userProjet->getAcronyme()] = $heuresPassees[$userProjet->getAcronyme()] ?? 0.0;
            if ($userProjet->getColorCode()) $codeColors[$userProjet->getAcronyme()] = $userProjet->getColorCode();
        }

        return [
            'userProjetsHeuresPassees' => $userProjetsHeuresPassees,
            'codeColors' => $codeColors,
        ];
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
        $codeColors = [];

        foreach ($societeUsers as $societeUser) {
            $heuresPassees = $this->calculateHeuresParProjet($societeUser->getSociete(), $year);
            $userProjets = $societeUser->isAdminFo()
                ? $this->projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $year, $year)
                : $this->projetRepository->findAllForUserInYear($societeUser, $roleMinimum, $year);

            $userProjetsHeuresPassees = [];

            foreach ($userProjets as $userProjet) {
                $userProjetsHeuresPassees["{$societeUser->getSociete()->getRaisonSociale()} / {$userProjet->getAcronyme()}"] = $heuresPassees[$userProjet->getAcronyme()] ?? 0.0;
                if ($userProjet->getColorCode()) $codeColors["{$societeUser->getSociete()->getRaisonSociale()} / {$userProjet->getAcronyme()}"] = $userProjet->getColorCode();
            }

            $multisosieteProjetsHeuresPassees = array_merge($multisosieteProjetsHeuresPassees, $userProjetsHeuresPassees);
        }

        return [
            'multisosieteProjetsHeuresPassees' => $multisosieteProjetsHeuresPassees,
            'codeColors' => $codeColors,
        ];
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
                $this->calculateWorkedHoursPerDay($tempsPasse)
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

            $hoursPerDay = $this->calculateWorkedHoursPerDay($tempsPasse);
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
                        $tempsPasses[$tempsPasse->getProjet()->getAcronyme()] = round(array_sum($this->calculateWorkedHoursPerDay($tempsPasse)), 1);break;
                    default:
                        $tempsPasses[$tempsPasse->getProjet()->getAcronyme()] = $tempsPasse->getPourcentage();break;
                }

            }

            $data[intval($cra->getMois()->format('m')) - 1] = $tempsPasses;
        }

        return $data;
    }

    /**
     * @return float
     */
    public function getTempsTotalParUserParProjet(SocieteUser $societeUser, Projet $projet): float
    {
        $tempsPasses = $this->tempsPasseRepository->findAllByProjetAndUser($projet, $societeUser);

        $tempsTotal = 0;
        foreach ($tempsPasses as $tempsPasse) {
            $tempsTotal += round(array_sum($this->calculateWorkedHoursPerDay($tempsPasse)), 1);
        }

        return $tempsTotal;
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
                    $data[$month][$user] = round(array_sum($this->calculateWorkedHoursPerDay($tempsPasse)), 1);break;
                default:
                    $data[$month][$user] = $tempsPasse->getPourcentage();break;
            }
        }

        return $data;
    }

    /**
     * @return float[] Lisser les heures de travaille par jours sur un mois
     */
    private function calculateWorkedHoursPerDay(TempsPasse $tempsPasse): array
    {
        $cra = $tempsPasse->getCra();
        $societeUser = $cra->getSocieteUser();
        $heuresParJours = Timesheet::getUserHeuresParJours($societeUser);

        $this->craService->uncheckJoursNotBelongingToSociete($cra, $societeUser);

        return array_map(
            function (float $presenceJour, int $key) use ($heuresParJours, $tempsPasse) {
                $day = (new \DateTime($tempsPasse->getCra()->getMois()->format('d-m-Y')))->modify("+$key days");

                if (!$tempsPasse->getProjet()->isProjetActiveInDate($day)) {
                    return 0.0;
                }

                return ($heuresParJours * $presenceJour * $tempsPasse->getPourcentage($key)) / 100.0;
            },
            $cra->getJours(),
            array_keys($cra->getJours())
        );
    }
}
