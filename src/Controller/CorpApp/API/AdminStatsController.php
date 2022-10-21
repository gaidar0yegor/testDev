<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Exception\BudgetAnalysisException;
use App\MultiSociete\UserContext;
use App\Repository\TempsPasseRepository;
use App\Security\Voter\TeamManagementVoter;
use App\Security\Voter\SameSocieteVoter;
use App\Service\BudgetAnalysisProjetService;
use App\Service\EquipeChecker;
use App\Service\StatisticsService;
use App\Service\Timesheet\TimesheetCalculator;
use App\Twig\DiffDateTimesExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Returns statistics about users or projets
 * to generate charts dedicated to the admin and superiors.
 *
 * @Route("/api/stats")
 */
class AdminStatsController extends AbstractController
{
    private StatisticsService $statisticsService;
    private EquipeChecker $equipeChecker;

    public function __construct(StatisticsService $statisticsService, EquipeChecker $equipeChecker)
    {
        $this->equipeChecker = $equipeChecker;
        $this->statisticsService = $statisticsService;
    }

    /**
     * Retourne les temps passés par un user sur ses projets sur une année.
     * Exemple :
     * {
     *      "months": [
     *          {
     *              "RDI-M": 35,
     *              "Group": 5
     *          },
     *          {
     *              "RDI-M": 40,
     *              "Group": 2
     *          },
     *          ...
     *      ]
     * }
     *
     * @Route(
     *      "/temps-par-projet/{id}/{year}/{unit}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}", "unit"="^[a-z]+$"},
     *      name="api_stats_admin_temps_user_projets"
     * )
     */
    public function getTempsUserParProjet(
        SocieteUser $societeUser,
        string $year,
        string $unit,
        UserContext $userContext
    ) {
        if ($societeUser !== $userContext->getSocieteUser()){
            $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);
        }

        $data = $this->statisticsService->getTempsUserParProjet($societeUser,$year,$unit);

        return new JsonResponse([
            'months' => $data,
        ]);
    }

    /**
     * Retourne les temps passés sur un projet par les contributeurs sur une année.
     * Exemple :
     * {
     *      "months": [
     *          {
     *              "User A": 35,
     *              "User B": 5
     *          },
     *          {
     *              "User A": 40,
     *              "User C": 2
     *          },
     *          ...
     *      ]
     * }
     *
     * @Route(
     *      "/temps-par-user/{id}/{year}/{unit}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}", "unit"="^[a-z]+$"},
     *      name="api_stats_admin_temps_projet_users"
     * )
     */
    public function getTempsProjetParUsers(
        Projet $projet,
        string $year,
        string $unit
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $projet);

        $data = $this->statisticsService->getTempsProjetParUsers($projet, $year, $unit);

        return new JsonResponse([
            'months' => $data,
        ]);
    }

    /**
     * @Route(
     *      "/budgets/{id}",
     *      methods={"GET"},
     *      name="api_stats_admin_budgets"
     * )
     */
    public function getBudgetsProjet(Projet $projet, BudgetAnalysisProjetService $budgetAnalysisProjetService)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $projet);

        try{
            $budgets = $budgetAnalysisProjetService->getBudgets($projet);
        } catch (BudgetAnalysisException $exception){
            return new JsonResponse(
                [ 'message' => $exception->getMessage() ],
                500
            );
        }

        return new JsonResponse([
            'budgets' => $budgets,
        ]);
    }

    /**
     * @Route(
     *      "/revenues/{id}",
     *      methods={"GET"},
     *      name="api_stats_admin_revenues"
     * )
     */
    public function getRevenuesProjet(Projet $projet, BudgetAnalysisProjetService $budgetAnalysisProjetService)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $projet);

        try{
            $roi = $budgetAnalysisProjetService->getRoi($projet);
        } catch (BudgetAnalysisException $exception){
            return new JsonResponse(
                [ 'message' => $exception->getMessage() ],
                500
            );
        }

        return new JsonResponse([
            'roi' => $roi,
        ]);
    }

    /**
     * @Route(
     *      "/tasks-status/{id}",
     *      methods={"GET"},
     *      name="api_stats_admin_tasks_status"
     * )
     */
    public function getTasksStatus(Projet $projet)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $projet);

        $stats = [
            'in_progress' => 0,
            'ended' => 0,
            'upcoming' => 0,
        ];
        foreach ($projet->getProjetPlanning()->getProjetPlanningTasks() as $planningTask){
            $stats[$planningTask->getStatut()]++;
        }

        return new JsonResponse($stats);
    }
}
