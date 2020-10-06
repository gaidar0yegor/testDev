<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateursFoController extends AbstractController
{
    /**
     * @Route("/utilisateurs/fo", name="utilisateurs_fo")
     */
    public function index()
    {
        return $this->render('utilisateurs_fo/index.html.twig', [
            'controller_name' => 'UtilisateursFoController',
        ]);
    }
}
