<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projets;
use App\Form\ProjetFormType;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Repository\ProjetsRepository;

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
        $liste_projets = $this->getDoctrine()->getRepository(Projets::class)->findAll();
        //  dd($projets);
        return $this->render('projets/liste_projets.html.twig', [
            'liste_projets'=> $liste_projets
        ]);

    }

    /**
     * @Route("/infos_projet", name="infos_projet_")
     */
    public function saisieInfosProjet(ProjetsRepository $pr)
    {
        $liste_projets = $pr->findAll();
        return $this->render('projets/saisie_infos_projet.html.twig', [
            'liste_projets' => $liste_projets,

        ]);
    }
}
