<?php

namespace App\DTO;

use App\Entity\Evenement;
use App\Entity\EvenementParticipant;

/**
 * Classe utilisée pour mettre à jour les absences
 * dans les cra des societeUsers
 */
class EvenementUpdatesCra
{
    private ?Evenement $oldEvenement = null;

    private ?Evenement $newEvenement = null;

    public function setOldEvenement(?Evenement $oldEvenement): self
    {
        $this->oldEvenement = $oldEvenement instanceof Evenement ? clone $oldEvenement : null;
        return $this;
    }

    public function setNewEvenement(?Evenement $newEvenement): self
    {
        $this->newEvenement = $newEvenement instanceof Evenement ? clone $newEvenement : null;
        return $this;
    }

    public function getOldEvenement(): ?Evenement
    {
        return $this->oldEvenement;
    }

    public function getNewEvenement(): ?Evenement
    {
        return $this->newEvenement;
    }

    public function getOldSocieteUsers(): array
    {
        if (null === $this->oldEvenement){
            return [];
        }

        $societeUsers = $this->oldEvenement->getRequiredEvenementParticipants()->map(function (EvenementParticipant $evenementParticipant){
            return $evenementParticipant->getSocieteUser();
        });

        return $societeUsers->toArray();
    }

    public function getNewSocieteUsers(): array
    {
        if (null === $this->newEvenement){
            return [];
        }

        $societeUsers = $this->newEvenement->getRequiredEvenementParticipants()->map(function (EvenementParticipant $evenementParticipant){
            return $evenementParticipant->getSocieteUser();
        });

        return $societeUsers->toArray();
    }

    public function getOldMonthsCraDays(): array
    {
        $monthsCraDays = [];

        if ($this->oldEvenement instanceof Evenement){
            for ($date = clone $this->oldEvenement->getStartDate(); $date <= $this->oldEvenement->getEndDate(); $date->modify('+1 day')) {
                $monthsCraDays[$date->format('Y')][$date->format('m')][] = (int)$date->format('j');
            }
        }

        return $monthsCraDays;
    }

    public function getNewMonthsCraDays(): array
    {
        $monthsCraDays = [];

        if ($this->newEvenement instanceof Evenement){
            for ($date = clone $this->newEvenement->getStartDate(); $date <= $this->newEvenement->getEndDate(); $date->modify('+1 day')) {
                $monthsCraDays[$date->format('Y')][$date->format('m')][] = (int)$date->format('j');
            }
        }

        return $monthsCraDays;
    }
}
