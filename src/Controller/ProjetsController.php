<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projets;
use App\Form\ProjetFormType;

class ProjetsController extends AbstractController
{
    /**
     * @Route("/projets", name="projets_")
     */
    public function listerProjets()
    {
        // return $this->render('projets/liste_projets.html.twig', [
        //     'controller_name' => 'ProjetsController',
        // ]);
        //    dd(4);
        $projets = $this->getDoctrine()->getRepository(Projets::class)->findAll();
        //  dd($projets);
        return $this->render('projets/liste_projets.html.twig', [
            'projets'=> $projets
        ]);



    }

    /**
     * @Route("/infos_projet", name="infos_projet_")
     */
    public function saisieInfosProjet()
    {
        return $this->render('projets/saisie_infos_projet.html.twig', [
            'controller_name' => 'ProjetsController',
        ]);
    }
}
