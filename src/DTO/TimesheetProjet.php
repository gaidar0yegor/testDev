<?php

namespace App\DTO;

use App\Entity\Cra;
use App\Entity\ProjetParticipant;
use App\Entity\TempsPasse;
use ArrayAccess;

/**
 * Value object qui représente une feuille de temps.
 */
class TimesheetProjet
{
    private ProjetParticipant $projetParticipant;

    private ?TempsPasse $tempsPasse;

    /**
     * Liste détaillée d'heures travaillées par jours dans le mois.
     * Exemple : [1.2, 1.2, 0, 0, 0.6, 1.2, ...]
     *
     * @var float[]
     */
    private ?array $workedHours;

    /**
     * Total d'heures passées sur ce projet dans le mois.
     */
    private ?float $totalWorkedHours;

    public function __construct(
        ProjetParticipant $projetParticipant,
        ?TempsPasse $tempsPasse,
        ?array $workedHours,
        ?float $totalWorkedHours
    ) {
        $this->projetParticipant = $projetParticipant;
        $this->tempsPasse = $tempsPasse;
        $this->workedHours = $workedHours;
        $this->totalWorkedHours = $totalWorkedHours;
    }

    public function getProjetParticipant(): ProjetParticipant
    {
        return $this->projetParticipant;
    }

    /**
     * Pour gagner en lisibilité dans les templates.
     */
    public function getParticipation(): ProjetParticipant
    {
        return $this->projetParticipant;
    }

    public function hasValue(): bool
    {
        return null !== $this->tempsPasse;
    }

    public function getTempsPasse(): ?TempsPasse
    {
        return $this->tempsPasse;
    }

    public function getWorkedHours(): ?array
    {
        return $this->workedHours;
    }

    public function getTotalWorkedHours(): ?float
    {
        return $this->totalWorkedHours;
    }
}
