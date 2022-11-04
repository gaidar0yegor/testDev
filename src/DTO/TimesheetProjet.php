<?php

namespace App\DTO;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\TempsPasse;

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

    public function __construct(
        ProjetParticipant $projetParticipant,
        ?TempsPasse $tempsPasse = null,
        ?array $workedHours = null
    ) {
        $this->projetParticipant = $projetParticipant;
        $this->tempsPasse = $tempsPasse;
        $this->workedHours = $workedHours;
    }

    public function getProjetParticipant(): ProjetParticipant
    {
        return $this->projetParticipant;
    }

    public function getProjet(): Projet
    {
        return $this->projetParticipant->getProjet();
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

    /**
     * @return float Nombre d'heures total travaillées sur ce projet dans le mois.
     */
    public function getTotalWorkedHours(): float
    {
        if (null === $this->workedHours) {
            return 0.0;
        }

        return array_sum($this->workedHours);
    }
}
