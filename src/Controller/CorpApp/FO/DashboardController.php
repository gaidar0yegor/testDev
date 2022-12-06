<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\User;
use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Projet;
use App\Repository\ProjetRepository;
use App\Repository\SocieteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private SocieteRepository $societeRepository;

    public function __construct(SocieteRepository $societeRepository)
    {
        $this->societeRepository = $societeRepository;
    }

    /**
     * @Route("/mon-tableau-de-bord", name="corp_app_fo_dashboard")
     */
    public function dashboard(UserContext $userContext)
    {
        if (!$userContext->hasSocieteUser()) {
            return $this->redirectToRoute('corp_app_fo_multi_societe_switch');
        }

        return $this->render('corp_app/dashboard/dashboard.html.twig');
    }

    /**
     * @Route(
     *      "/mon-tableau-de-bord-api",
     *      methods={"GET"},
     *      name="dashboard_societe"
     * )
     */
    public function getPdfInfos(SocieteRepository $societeRepository): Response
    {

        // $data = 

        return new JsonResponse([
            // 'data' => $data,
        ]);
    }
}
