<?php

namespace App\Controller\FO;

use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/mon-tableau-de-bord", name="app_fo_dashboard")
     */
    public function dashboard(UserContext $userContext)
    {
        if (!$userContext->hasSocieteUser()) {
            return $this->redirectToRoute('app_fo_multi_societe_switch');
        }

        return $this->render('dashboard/dashboard.html.twig');
    }
}
