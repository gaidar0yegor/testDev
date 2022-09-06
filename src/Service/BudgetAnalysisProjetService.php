<?php

namespace App\Service;

use App\Entity\Cra;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\ProjetRevenue;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Exception\BudgetAnalysisException;
use App\Exception\TimesheetException;
use App\Service\StatisticsService;

/**
 * Value object qui représente une feuille de temps.
 */
class BudgetAnalysisProjetService
{
    /**
     * convertir ETP -> Heure
     */
    public const COEF_CONVERT = 1607;

    private StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function getBudgets(Projet $projet): array
    {
        return [
            'heure' => $this->getBudgetEnHeure($projet),
            'euro' => [
                'prev' => $this->getPrevBudgetEnEuro($projet),
                'reel' => $this->getReelBudgetEnEuro($projet)
            ]
        ];
    }

    public function getRoi(Projet $projet): array
    {
        return [
            'revenue' => $this->getReelRevenueEnEuro($projet),
            'expense' => $this->getReelBudgetEnEuro($projet)
        ];
    }

    public function getBudgetEnHeure(Projet $projet): array
    {
        $projetPeriod = abs(round(($projet->getDateDebut()->getTimestamp() - $projet->getDateFin()->getTimestamp()) / (60 * 60 * 24)));

        $budgetPrev = intval($projet->getEtp() * ($projetPeriod / 365) * self::COEF_CONVERT);

        $budgetReel = 0;
        for($year = $projet->getDateDebut()->format('Y'); $year <= (new \DateTime())->format('Y'); $year++){
            $tempsByMonths = $this->statisticsService->getTempsProjetParUsers($projet,$year,'hour');
            foreach ($tempsByMonths as $month){
                $budgetReel += array_sum($month);
            }
        }

        return [
            'prev' => $budgetPrev,
            'reel' => $budgetReel
        ];
    }

    public function getPrevBudgetEnEuro(Projet $projet): int
    {
        return $projet->getBudgetEuro() ? (int)$projet->getBudgetEuro() : 0;
    }

    public function getReelBudgetEnEuro(Projet $projet): float
    {
        $usersCoutHoraire = $projet->getProjetParticipants()->map(function (ProjetParticipant $participant){
            return self::getUserCoutEtp($participant->getSocieteUser()) * $this->statisticsService->getTempsTotalParUserParProjet($participant->getSocieteUser(), $participant->getProjet());
        });

        $budgetReel = array_sum($usersCoutHoraire->toArray());

        foreach ($projet->getProjetBudgetExpenses() as $expense){
            $budgetReel += $expense->getAmount();
        }

        return $budgetReel;
    }

    public function getReelRevenueEnEuro(Projet $projet): float
    {
        $revenues = $projet->getProjetRevenues()->map(function (ProjetRevenue $projetRevenue){
            return $projetRevenue->getAmount();
        });
        return array_sum($revenues->toArray());
    }

    /**
     * @param SocieteUser $societeUser
     * @return float
     * @throws BudgetAnalysisException
     */
    private function getUserCoutEtp(SocieteUser $societeUser): float
    {
        if (null !== $societeUser->getCoutEtp()) {
            return $societeUser->getCoutEtp();
        }

        if (null === $societeUser->getSociete()) {
            throw new BudgetAnalysisException(
                'Impossible de générer une Analyse budgétaire : '.
                'L\'utilisateur n\'a pas de coût d\'ETP, est n\'est pas dans une société'
            );
        }

        if (null !== $societeUser->getSociete()->getCoutEtp()) {
            return $societeUser->getSociete()->getCoutEtp();
        }

        throw new BudgetAnalysisException(
            'Impossible de générer une Analyse budgétaire.'
            .' Veuillez définir le coût d\'ETP par défaut pour la société'
        );
    }
}
