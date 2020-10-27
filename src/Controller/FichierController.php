<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\FichierProjet;
use App\Repository\FichiersProjetRepository;

class FichierController extends AbstractController
{
    /**
     * @Route("/liste/fichiers", name="liste_fichiers_")
     */
    public function listeFichiers(FichiersProjetRepository $fr)
    {
        $liste_fichiers = $fr->findAll();
        return $this->render('fichier/liste_fichiers.html.twig', [
            'liste_fichiers' => $liste_fichiers,
            // 'controller_name' => 'FichierController',
        ]);
    }
}
