<?php

namespace App\Service\Timesheet;

use App\DTO\FilterTimesheet;
use App\DTO\Timesheet;
use App\DTO\TimesheetProjet;
use App\Entity\Cra;
use App\Entity\ProjetParticipant;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Exception\TimesheetException;
use App\Service\DateMonthService;

class TimesheetCalculator
{
    private UserContributingProjetRepositoryInterface $participationRepository;

    private UserMonthCraRepositoryInterface $craRepository;

    private DateMonthService $dateMonthService;

    public function __construct(
        UserContributingProjetRepositoryInterface $participationRepository,
        UserMonthCraRepositoryInterface $craRepository,
        DateMonthService $dateMonthService
    ) {
        $this->participationRepository = $participationRepository;
        $this->craRepository = $craRepository;
        $this->dateMonthService = $dateMonthService;
    }

    public function generateTimesheetProjet(ProjetParticipant $participation, Cra $cra): TimesheetProjet
    {
        $tempsPasse = $this->getTempsPassesOnProjet($cra->getUser(), $participation);

        if (null === $tempsPasse) {
            return new TimesheetProjet($participation);
        }

        $heuresParJours = Timesheet::getUserHeuresParJours($cra->getUser());
        $totalJours = 0;
        $workedHours = array_map(
            function (float $presenceJour, int $key) use ($heuresParJours, $tempsPasse, $totalJours, $participation, $cra) {
                $projet = $participation->getProjet();
                $day = (new \DateTime($cra->getMois()->format('d-m-Y')))->modify("+$key days");

                if (!$projet->isProjetActiveInDate($day)) {
                    return 0.0;
                }

                $totalJours += $presenceJour;
                return ($heuresParJours * $presenceJour * $tempsPasse->getPourcentage()) / 100.0;
            },
            $cra->getJours(),
            array_keys($cra->getJours())
        );

        return new TimesheetProjet(
            $participation,
            $tempsPasse,
            $workedHours
        );
    }

    public function generateTimesheet(User $user, \DateTime $month): Timesheet
    {
        $this->dateMonthService->normalize($month);

        $cra = $this->craRepository->findCraByUserAndMois($user, $month);

        if (null === $cra) {
            throw new TimesheetException(sprintf(
                'Impossible de générer la feuille de temps :'
                .' l\'utilisateur "%s" n\'a pas rempli ses jours de congés du mois %s.',
                $user->getEmail(),
                $month->format('Y/m')
            ));
        }

        $participations = $this->participationRepository->findProjetsContributingUser($cra->getUser());

        $timesheetProjets = [];

        foreach ($participations as $participation) {
            if (!$this->dateMonthService->isProjetActiveInMonth($participation->getProjet(), $month)) {
                continue;
            }

            $timesheetProjets[] = $this->generateTimesheetProjet($participation, $cra);
        }

        return new Timesheet(
            $cra,
            $timesheetProjets
        );
    }

    /**
     * @return Timesheet[]
     */
    public function generateMultipleTimesheets(FilterTimesheet $filter): array
    {
        $from = $filter->getFrom();
        $to = $filter->getTo();

        $this->dateMonthService->normalize($from);
        $this->dateMonthService->normalize($to);

        $timesheets = [];

        foreach ($filter->getUsers() as $user) {
            for ($month = $from; $month <= $to; $month = $this->dateMonthService->getNextMonth($month)) {
                try {
                    $timesheets[] = $this->generateTimesheet($user, $month);
                } catch (TimesheetException $e) {
                }
            }
        }

        return $timesheets;
    }

    private function getTempsPassesOnProjet(User $user, ProjetParticipant $projetParticipant): ?TempsPasse
    {
        $tempsPasses = $user->getTempsPasses()->filter(function (TempsPasse $tempsPasse) use ($projetParticipant) {
            return $tempsPasse->getProjet() === $projetParticipant->getProjet();
        });

        if (0 === $tempsPasses->count()) {
            return null;
        }

        return $tempsPasses->first();
    }
}
