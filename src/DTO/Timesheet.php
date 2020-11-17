<?php

namespace App\DTO;

use App\Entity\Cra;
use App\Entity\User;
use App\Exception\TimesheetException;

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

    public function getHeuresParJours(): float
    {
        return self::getUserHeuresParJours($this->cra->getUser());
    }

    /**
     * Retrouve le nombre d'heures travaillées par jour pour un user.
     * Se base sur la valeur globalement défini par la société,
     * ou d'heures personnalisé pour l'utilisateur.
     *
     * @throws TimesheetException Si le nombre d'heure par jour n'est pas défini,
     *      ni globalement pour la société, ni pour cet utilisateur.
     */
    public static function getUserHeuresParJours(User $user): float
    {
        if (null !== $user->getHeuresParJours()) {
            return $user->getHeuresParJours();
        }

        if (null !== $user->getSociete()->getHeuresParJours()) {
            return $user->getSociete()->getHeuresParJours();
        }

        throw new TimesheetException(
            'Impossible de générer une feuille de temps sans connaître le nombre d\'heures par jour.'
            .' Veuillez définir un nombre d\'heure par défaut pour la société'
        );
    }

    public function getTotalPourcentage(): int
    {
        $total = 0;

        foreach ($this->timesheetProjets as $timesheetProjet) {
            $tempsPasse = $timesheetProjet->getTempsPasse();

            if (null === $tempsPasse) {
                continue;
            }

            $total += $tempsPasse->getPourcentage();
        }

        return $total;
    }
}
