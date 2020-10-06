<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SocietesController extends AbstractController
{
    /**
     * @Route("/societes", name="societes")
     */
    public function index()
    {
        return $this->render('societes/index.html.twig', [
            'controller_name' => 'SocietesController',
        ]);
    }
}
