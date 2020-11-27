<?php

namespace App\Service;

use App\Entity\Societe;
use App\Repository\TempsPasseRepository;
use App\Service\Timesheet\TimesheetCalculator;
use SplObjectStorage;

class StatisticsService
{
    private TempsPasseRepository $tempsPasseRepository;

    private TimesheetCalculator $timesheetCalculator;

    public function __construct(
        TempsPasseRepository $tempsPasseRepository,
        TimesheetCalculator $timesheetCalculator
    ) {
        $this->tempsPasseRepository = $tempsPasseRepository;
        $this->timesheetCalculator = $timesheetCalculator;
    }

    /**
     * @return array With tuple of [string $projetAcronyme, float $heuresPassees][]
     */
    public function calculateHeuresParProjet(Societe $societe, int $year)
    {
        $tempsPasses = $this->tempsPasseRepository->findAllBySocieteAndProjet($societe, $year);
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
