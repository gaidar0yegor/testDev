<?php

namespace App\Controller\FO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/mon-tableau-de-bord", name="app_fo_dashboard")
     */
    public function dashboard()
    {
        return $this->render('dashboard/dashboard.html.twig');
    }
}
