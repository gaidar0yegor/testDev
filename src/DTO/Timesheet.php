<?php

namespace App\DTO;

use App\Entity\Cra;
use App\Entity\ProjetParticipant;
use ArrayAccess;

/**
 * Value object qui représente une feuille de temps.
 */
class Timesheet
{
    private Cra $cra;

    /**
     * @param TimesheetProjet[]
     */
    private array $timesheetProjets;

    /**
     * Nombre de jours travaillés dans le mois.
     */
    private float $totalJours;

    public function __construct(Cra $cra, array $timesheetProjets, float $totalJours)
    {
        $this->cra = $cra;
        $this->timesheetProjets = $timesheetProjets;
        $this->totalJours = $totalJours;
    }

    public function getCra(): Cra
    {
        return $this->cra;
    }

    public function getTimesheetProjet(): array
    {
        return $this->timesheetProjets;
    }

    public function getTotalJours(): float
    {
        return $this->totalJours;
    }

    /**
     * @return float Total des heures travaillés sur les projets ce mois ci.
     */
    public function getTotalWorkedHours(): float
    {
        $total = 0;

        foreach ($this->timesheetProjets as $timesheetProjet) {
            $total += $timesheetProjet->getTotalWorkedHours();
        }

        return $total;
    }

    /**
     * Pour gagner en lisibilité dans les templates.
     */
    public function getProjets(): array
    {
        return $this->timesheetProjets;
    }
}
