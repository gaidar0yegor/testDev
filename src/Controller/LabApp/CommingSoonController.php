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
        return $this->render('_comming_soon.html.twig');
    }
}
