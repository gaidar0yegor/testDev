<?php

namespace App\Controller\FO\Admin;

use App\Entity\Projet;
use App\Repository\ProjetRepository;
use App\MultiSociete\UserContext;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjetController extends AbstractController
{
    /**
     * Affichage de tous les projets de la société
     *
     * @Route("/tous-les-projets", name="app_fo_admin_projets")
     */
    public function listerProjetAdmin(ProjetRepository $projetRepository, UserContext $userContext)
    {
        $projets = $projetRepository->findAllProjectsPerSociete($userContext->getSocieteUser()->getSociete());
        $yearRange = $projetRepository->findProjetsYearRangeFor($userContext->getSocieteUser());

        return $this->render('projets/admin_liste_projets.html.twig', [
            'projets'=> $projets,
            'yearMin' => $yearRange['yearMin'] ?? date('Y'),
            'yearMax' => $yearRange['yearMax'] ?? date('Y'),
        ]);
    }

    /**
     * Page admin d'un projet.
     *
     * @Route("/projets/{id}", name="app_fo_admin_projet")
     */
    public function projetManage(Projet $projet)
    {
        return $this->render('projets/admin_manage.html.twig', [
            'projet'=> $projet,
        ]);
    }
}
