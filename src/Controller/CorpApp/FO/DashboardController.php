<?php

namespace App\Controller\CorpApp\FO;

use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
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
}
