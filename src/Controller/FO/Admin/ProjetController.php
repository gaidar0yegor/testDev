<?php

namespace App\Controller\FO\Admin;

use App\Repository\ProjetRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjetController extends AbstractController
{
    /**
     * Affichage de tous les projets de la société
     *
     * @Route("/tous-les-projets", name="app_fo_admin_projets")
     */
    public function listerProjetAdmin(ProjetRepository $projetRepository)
    {
        $allProjectsOfSociete = $projetRepository->findAllProjectsPerSociete($this->getUser()->getSociete());
        return $this->render('projets/admin_liste_projets.html.twig', [
            'projets'=> $allProjectsOfSociete,
        ]);
    }
}
