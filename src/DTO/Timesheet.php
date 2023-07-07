<?php

namespace App\DTO;

use App\Entity\Cra;
use App\Entity\SocieteUser;
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

    public function __construct(Cra $cra, array $timesheetProjets)
    {
        $this->cra = $cra;
        $this->timesheetProjets = $timesheetProjets;
    }

    public function getCra(): Cra
    {
        return $this->cra;
    }

    /**
     * @return TimesheetProjet[]
     */
    public function getTimesheetProjets(): array
    {
        return $this->timesheetProjets;
    }

    public function getTotalJours(): float
    {
        return array_sum($this->cra->getJours());
    }

    public function getSumJourPresence(): int
    {
        return count(array_filter(
            $this->cra->getJours(),
            function (float $n) {
                return $n >= 1;
            }
        ));
    }

    public function getSumJourDemiJournees(): float
    {
        return count(array_filter(
            $this->cra->getJours(),
            function (float $n) {
                return $n > 0 && $n < 1;
            }
        ));
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
        return self::getUserHeuresParJours($this->cra->getSocieteUser());
    }

    /**
     * Retrouve le nombre d'heures travaillées par jour pour un user.
     * Se base sur la valeur globalement défini par la société,
     * ou d'heures personnalisé pour l'utilisateur.
     *
     * @throws TimesheetException Si le nombre d'heure par jour n'est pas défini,
     *      ni globalement pour la société, ni pour cet utilisateur.
     */
    public static function getUserHeuresParJours(SocieteUser $societeUser): float
    {
        if (null !== $societeUser->getHeuresParJours()) {
            return $societeUser->getHeuresParJours();
        }

        if (null === $societeUser->getSociete()) {
            throw new TimesheetException(
                'Impossible de générer une feuille de temps : '.
                'L\'utilisateur n\'a pas de nombre d\'heures par jours, est n\'est pas dans une société'
            );
        }

        if (null !== $societeUser->getSociete()->getHeuresParJours()) {
            return $societeUser->getSociete()->getHeuresParJours();
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
