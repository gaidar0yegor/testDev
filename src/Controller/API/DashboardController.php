<?php

namespace App\Controller\API;

use App\Service\CraService;
use App\Service\DateMonthService;
use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * Retourne si l'utilisateur est à jour dans la saisie de ses temps.
     *
     * @Route(
     *      "/temps-du-mois",
     *      methods={"GET"},
     *      name="api_dashboard_temps_du_mois"
     * )
     */
    public function getTempsDuMois(CraService $craService, DateMonthService $dateMonthService)
    {
        $cra = $craService->loadCraForUser(
            $this->getUser(),
            $dateMonthService->getCurrentMonth()
        );

        return new JsonResponse([
            'isCraSubmitted' => $cra->isCraSubmitted(),
            'isTempsPassesSubmitted' => $cra->isTempsPassesSubmitted(),
            'hasTempsPasses' => $cra->hasTempsPasses(),

            'craModifiedAt' => null === $cra->getCraModifiedAt()
                ? null
                : $cra->getCraModifiedAt()->format('d M Y')
            ,

            'tempsPassesModifiedAt' => null === $cra->getTempsPassesModifiedAt()
                ? null
                : $cra->getTempsPassesModifiedAt()->format('d M Y')
            ,
        ]);
    }

    /**
     * Retourne le nombre d'heures passées par projet dans cette année.
     *
     * @Route(
     *      "/heures-par-projet/{year}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}"},
     *      name="api_dashboard_heures_passees_par_projet"
     * )
     */
    public function getHeuresPasseesParProjet(int $year, StatisticsService $statisticsService)
    {
        $heuresParProjet = $statisticsService->calculateHeuresParProjet(
            $this->getUser()->getSociete(),
            $year
        );

        return new JsonResponse($heuresParProjet);
    }
}
