<?php

namespace App\Service\Timesheet;

use App\DTO\FilterTimesheet;
use App\DTO\Timesheet;
use App\DTO\TimesheetProjet;
use App\Entity\Cra;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Exception\TimesheetException;
use App\Service\CraService;
use App\Service\DateMonthService;

class TimesheetCalculator
{
    private UserContributingProjetRepositoryInterface $participationRepository;

    private UserMonthCraRepositoryInterface $craRepository;

    private CraService $craService;

    private DateMonthService $dateMonthService;

    public function __construct(
        UserContributingProjetRepositoryInterface $participationRepository,
        UserMonthCraRepositoryInterface $craRepository,
        CraService $craService,
        DateMonthService $dateMonthService
    ) {
        $this->participationRepository = $participationRepository;
        $this->craRepository = $craRepository;
        $this->craService = $craService;
        $this->dateMonthService = $dateMonthService;
    }

    public function generateTimesheetProjet(ProjetParticipant $participation, Cra $cra): TimesheetProjet
    {
        $tempsPasse = $this->getTempsPassesOnProjet($cra, $participation);

        if (null === $tempsPasse) {
            return new TimesheetProjet($participation);
        }

        $workedHours = $this->calculateWorkedHoursPerDay($tempsPasse);

        return new TimesheetProjet(
            $participation,
            $tempsPasse,
            $workedHours
        );
    }

    public function generateTimesheet(SocieteUser $societeUser, \DateTime $month): Timesheet
    {
        $month = $this->dateMonthService->normalize($month);
        $cra = $this->craRepository->findCraByUserAndMois($societeUser, $month);

        if (null === $cra) {
            throw new TimesheetException(sprintf(
                'Impossible de générer la feuille de temps :'
                .' l\'utilisateur "%s" n\'a pas rempli ses jours de congés du mois %s.',
                $societeUser->getUser()->getEmail(),
                $month->format('Y/m')
            ));
        }

        $participations = $this->participationRepository->findProjetsContributingUser($cra->getSocieteUser());
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
        $from = $this->dateMonthService->normalize($filter->getFrom());
        $to = $this->dateMonthService->normalize($filter->getTo());

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

    /**
     * @return float[] Grille d'heure par jours sur un mois
     */
    public function calculateWorkedHoursPerDay(TempsPasse $tempsPasse): array
    {
        $cra = $tempsPasse->getCra();
        $societeUser = $cra->getSocieteUser();
        $heuresParJours = Timesheet::getUserHeuresParJours($societeUser);

        $this->craService->uncheckJoursAvantDateEntree($cra, $societeUser);
        $this->craService->uncheckJoursApresDateSortie($cra, $societeUser);

        return array_map(
            function (float $presenceJour, int $key) use ($heuresParJours, $tempsPasse) {
                $day = (new \DateTime($tempsPasse->getCra()->getMois()->format('d-m-Y')))->modify("+$key days");

                if (!$tempsPasse->getProjet()->isProjetActiveInDate($day)) {
                    return 0.0;
                }

                return ($heuresParJours * $presenceJour * $tempsPasse->getPourcentage()) / 100.0;
            },
            $cra->getJours(),
            array_keys($cra->getJours())
        );
    }

    private function getTempsPassesOnProjet(Cra $cra, ProjetParticipant $projetParticipant): ?TempsPasse
    {
        $tempsPasses = $cra->getTempsPasses()->filter(function (TempsPasse $tempsPasse) use ($projetParticipant) {
            return $tempsPasse->getProjet() === $projetParticipant->getProjet();
        });

        if (0 === $tempsPasses->count()) {
            return null;
        }

        return $tempsPasses->first();
    }
}
