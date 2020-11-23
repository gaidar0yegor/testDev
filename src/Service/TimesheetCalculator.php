<?php

namespace App\Service;

use App\DTO\FilterTimesheet;
use App\DTO\Timesheet;
use App\DTO\TimesheetProjet;
use App\Entity\ProjetParticipant;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Exception\TimesheetException;
use App\Repository\CraRepository;
use App\Role;

class TimesheetCalculator
{
    private ParticipantService $participantService;

    private CraRepository $craRepository;

    private DateMonthService $dateMonthService;

    public function __construct(
        ParticipantService $participantService,
        CraRepository $craRepository,
        DateMonthService $dateMonthService
    ) {
        $this->participantService = $participantService;
        $this->craRepository = $craRepository;
        $this->dateMonthService = $dateMonthService;
    }

    public function generateTimesheet(User $user, \DateTime $month): Timesheet
    {
        $this->dateMonthService->normalize($month);

        $cra = $this->craRepository->findOneBy([
            'user' => $user,
            'mois' => $month,
        ]);

        if (null === $cra) {
            throw new TimesheetException(sprintf(
                'Impossible de générer la feuille de temps :'
                .' l\'utilisateur "%s" n\'a pas rempli ses jours de congés du mois %s.',
                $user->getEmail(),
                $month->format('Y/m')
            ));
        }

        $participations = $this->participantService->getProjetParticipantsWithRole(
            $cra->getUser()->getProjetParticipants(),
            Role::CONTRIBUTEUR
        );

        $timesheetProjets = [];
        $totalJours = array_sum($cra->getJours());
        $heuresParJours = Timesheet::getUserHeuresParJours($cra->getUser());

        foreach ($participations as $participation) {
            $tempsPasse = $this->getTempsPassesOnProjet($cra->getUser(), $participation);
            $workedHours = null;
            $totalWorkedHours = 0;

            if (null !== $tempsPasse) {
                $workedHours = array_map(function (float $presenceJour) use ($heuresParJours, $tempsPasse) {
                    return ($heuresParJours * $presenceJour * $tempsPasse->getPourcentage()) / 100.0;
                }, $cra->getJours());

                $totalWorkedHours = ($heuresParJours * $totalJours * $tempsPasse->getPourcentage()) / 100.0;
            }

            $timesheetProjets[] = new TimesheetProjet(
                $participation,
                $tempsPasse,
                $workedHours,
                $totalWorkedHours
            );
        }

        return new Timesheet(
            $cra,
            $timesheetProjets,
            $totalJours
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
