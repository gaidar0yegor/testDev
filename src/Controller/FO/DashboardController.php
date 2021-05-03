<?php

namespace App\Controller\FO;

use App\MultiSociete\UserContext;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/mon-tableau-de-bord", name="app_fo_dashboard")
     */
    public function dashboard(UserContext $userContext)
    {
        throw new Exception('arg');

        if (!$userContext->hasSocieteUser()) {
            return $this->redirectToRoute('app_fo_multi_societe_switch');
        }

        return $this->render('dashboard/dashboard.html.twig');
    }
}
