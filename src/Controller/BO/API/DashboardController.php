<?php

namespace App\Controller\BO\API;

use App\Repository\UserRepository;
use App\Repository\ProjetRepository;
use App\Repository\SocieteRepository;
use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Repository\TempsPasseRepository;
use App\Security\Voter\TeamManagementVoter;
use App\Security\Voter\SameSocieteVoter;
use App\Service\EquipeChecker;
use App\Service\StatisticsService;
use App\Service\Timesheet\TimesheetCalculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class DashboardController extends AbstractController
{
    private ProjetRepository $projetRepository;
    private UserRepository $userRepository;
    private SocieteRepository $societeRepository;

    public function __construct(ProjetRepository $projetRepository,UserRepository $userRepository, SocieteRepository $societeRepository)
    {
        $this->userRepository = $userRepository;
        $this->projetRepository = $projetRepository;
        $this->societeRepository = $societeRepository;
    }

    /**
     * @Route(
     *      "/historique-user",
     *      methods={"GET"},
     *      name="bo_api_historique_user"
     * )
     */
    public function historiqueUser()
    {
        for($year = 2020; $year <= (new \DateTime())->format('Y'); $year++){
            $data[$year] = []; 
            $usersByMonth = $this->userRepository->findCreatedAt($year);

            for($month = 1; $month <= 12; $month++){
                $key = array_search($month, array_column($usersByMonth, 'mois'));

                $data[$year][$month] = $key === false ? 0 : $usersByMonth[$key]['total'];
            }
        }

        return new JsonResponse([
            'data' => $data,
            'axes' => $this->generateChartAxes($data),
        ]);
    }

    /**
     * @Route(
     *      "/historique-societe",
     *      methods={"GET"},
     *      name="bo_api_historique_societe"
     * )
     */
    public function historiqueSociete()
    {
        for($year = 2020; $year <= (new \DateTime())->format('Y'); $year++){
            $data[$year] = []; 
            $societesByMonth = $this->societeRepository->findCreatedAt($year);

            for($month = 1; $month <= 12; $month++){
                $key = array_search($month, array_column($societesByMonth, 'mois'));

                $data[$year][$month] = $key === false ? 0 : $societesByMonth[$key]['total'];
            }
        }

        return new JsonResponse([
            'data' => $data,
            'axes' => $this->generateChartAxes($data),
        ]);
    }

    /**
     * @Route(
     *      "/historique-projet",
     *      methods={"GET"},
     *      name="bo_api_historique_projet"
     * )
     */
    public function historiqueProjet()
    {
        for($year = 2020; $year <= (new \DateTime())->format('Y'); $year++){
            $data[$year] = []; 
            $projetsByMonth = $this->projetRepository->findCreatedAt($year);

            for($month = 1; $month <= 12; $month++){
                $key = array_search($month, array_column($projetsByMonth, 'mois'));

                $data[$year][$month] = $key === false ? 0 : $projetsByMonth[$key]['total'];
            }
        }
        
        return new JsonResponse([
            'data' => $data,
            'axes' => $this->generateChartAxes($data),
        ]);
    }

    private function generateChartAxes(array $data)
    {
        $axes = [
            'x' => [],
            'y' => []
        ];

        foreach($data as $year => $months){
            foreach($months as $month => $value){
                $axes['x'][] = $month . '-' . $year;
                $axes['y'][] = $value;
            }
        }

        return $axes;
    }
}
