<?php

namespace App\Controller\LabApp\FO;

use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/mon-tableau-de-bord", name="lab_app_fo_dashboard")
     */
    public function dashboard(UserContext $userContext)
    {
        if (!$userContext->hasUserBook()) {
            return $this->redirectToRoute('lab_app_fo_multi_user_book_switch');
        }

        return $this->render('lab_app/dashboard/dashboard.html.twig');
    }
}
