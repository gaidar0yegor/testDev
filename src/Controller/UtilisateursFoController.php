<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateursFoController extends AbstractController
{
    /**
     * @Route("/utilisateurs/fo", name="utilisateurs_fo_")
     */
    public function listerUtilisateurs()
    {
        return $this->render('utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'controller_name' => 'UtilisateursFoController',
        ]);
    }

    /**
     * @Route("/utilisateurs/fo/infos", name="infos_utilisateur_fo_")
     */
    public function infosUtilisateur()
    {
        return $this->render('utilisateurs_fo/infos_utilisateur_fo.html.twig', [
            'controller_name' => 'UtilisateursFoController',
        ]);
    }


     /**
     * @Route("/utilisateurs/compte", name="compte_")
     */
    public function compteUtilisateur()
    {
        return $this->render('utilisateurs_fo/compte_utilisateur_fo.html.twig', [
            'controller_name' => 'UtilisateursFoController',
        ]);
    }
}
