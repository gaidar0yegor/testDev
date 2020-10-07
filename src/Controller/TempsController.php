<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TempsController extends AbstractController
{
    /**
     * @Route("/temps", name="temps_")
     */
    public function saisieTempsEnPourCent()
    {
        return $this->render('temps/temps_en_pour_cent.html.twig', [
            'controller_name' => 'TempsController',
        ]);
    }

    /**
     * @Route("/absences", name="absences_")
     */
    public function saisieAbsences()
    {
        return $this->render('temps/absences.html.twig', [
            'controller_name' => 'TempsController',
        ]);
    }
}
