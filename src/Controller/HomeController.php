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
            if(!$userContext->hasUserBook() && $userContext->hasSocieteUser()) {
                return $this->redirectToRoute('corp_app_fo_dashboard');
            }

            if (str_contains($this->getParameter('router.request_context.host'), 'afpa')){
                if($userContext->hasUserBook() && !$userContext->hasSocieteUser()) {
                    return $this->redirectToRoute('lab_app_fo_dashboard');
                }

                return $this->redirectToRoute('app_fo_multi_platefrom_switch');
            } else {
                return $this->redirectToRoute('corp_app_fo_multi_societe_switch');
            }
        }
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/mes-plateformes", name="app_fo_multi_platefrom_switch")
     */
    public function plateforms(UserContext $userContext): Response
    {
        if ($this->isGranted('ROLE_FO_USER')) {
            return $this->render('security/switch_multi_plateforms.html.twig');
        }
        return $this->redirectToRoute('app_login');
    }
}
