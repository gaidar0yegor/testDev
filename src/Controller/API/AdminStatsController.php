<?php

namespace App\Controller\API;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Repository\CraRepository;
use App\Repository\TempsPasseRepository;
use App\Security\Voter\SameSocieteVoter;
use App\Service\StatisticsService;
use App\Service\Timesheet\TimesheetCalculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

/**
 * Returns statistics about users or projets
 * to generate charts dedicated to the admin.
 *
 * @IsGranted("SOCIETE_ADMIN")
 * @Route("/api/stats/admin")
 */
class AdminStatsController extends AbstractController
{
    private StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
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
        string $unit
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

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
        string $unit,
        TempsPasseRepository $tempsPasseRepository,
        TimesheetCalculator $timesheetCalculator
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $projet);

        $data = $this->statisticsService->getTempsProjetParUsers($projet, $year, $unit);

        return new JsonResponse([
            'months' => $data,
        ]);
    }
}
