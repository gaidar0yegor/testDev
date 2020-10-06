<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TempsController extends AbstractController
{
    /**
     * @Route("/temps", name="temps")
     */
    public function index()
    {
        return $this->render('temps/index.html.twig', [
            'controller_name' => 'TempsController',
        ]);
    }
}
