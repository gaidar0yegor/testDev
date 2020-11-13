<?php

namespace App\Service;

use App\DTO\Timesheet;
use App\DTO\TimesheetProjet;
use App\Entity\Cra;
use App\Entity\ProjetParticipant;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Role;
use ArrayAccess;
use SplObjectStorage;

class TimesheetCalculator
{
    private ParticipantService $participantService;

    public function __construct(ParticipantService $participantService)
    {
        $this->participantService = $participantService;
    }

    public function generateTimesheet(Cra $cra): Timesheet
    {
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
            $totalWorkedHours = null;

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
