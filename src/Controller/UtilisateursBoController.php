<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateursBoController extends AbstractController
{
    /**
     * @Route("/utilisateurs/bo", name="utilisateurs_bo")
     */
    public function index()
    {
        return $this->render('utilisateurs_bo/index.html.twig', [
            'controller_name' => 'UtilisateursBoController',
        ]);
    }
}
