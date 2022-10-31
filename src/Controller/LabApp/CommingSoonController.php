<?php

namespace App\Controller\LabApp;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommingSoonController extends AbstractController
{
    /**
     * @Route("/comming-soon", name="lab_app_comming_soon")
     */
    public function index(): Response
    {
        if (str_contains($this->getParameter('router.request_context.host'), 'afpa')){
            return $this->redirectToRoute('lab_app_fo_multi_user_book_switch');
        }
        return $this->render('_comming_soon.html.twig');
    }
}
