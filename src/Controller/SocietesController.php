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
        return $this->render('societes/liste_societes.html.twig', [
            'controller_name' => 'SocietesController',
        ]);
    }

    /**
     * @Route("/infos_societe", name="infos_societe_")
     */
    public function saisieInfosSocietet()
    {
        return $this->render('societes/saisie_infos_societe.html.twig', [
            'controller_name' => 'SocietesController',
        ]);
    }

}
