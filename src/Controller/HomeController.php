<?php

namespace App\Controller;

use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function home(UserContext $userContext): Response
    {
        if ($this->isGranted('ROLE_FO_USER')) {
            if (!$userContext->hasSocieteUser()) {
                return $this->redirectToRoute('app_fo_multi_societe_switch');
            }

            return $this->redirectToRoute('app_fo_dashboard');
        }

        return $this->redirectToRoute('app_login');
    }
}
