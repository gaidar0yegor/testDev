<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Repository\UsersRepository;



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
    public function compteUtilisateur(UsersRepository $ur)
    {
        $liste_utilisateurs = $ur->findAll();
        return $this->render('utilisateurs_fo/compte_utilisateur_fo.html.twig', [
            'liste_utilisateurs' => $liste_utilisateurs,
        ]);
    }

    /**
     * @Route("/utilisateurs/fo/modifier/{id}", name="utilisateurs_fo_modifier", requirements={"id"="\d+"})
     */
    // public function modifier(Request $rq, EntityManager $em, ArtistRepository $ar, $id)
    // {
    //     // Modifier un utilisateur existant
    //     // récupérer l'utilisateur avec son id, l'afficher dans le formulaire et enregistrer les modifications dans la base de données
    //     $utilisateurAmodifier = $ar->find($id);
    //     $formArtiste = $this->createForm(ArtistType::class, $utilisateurAmodifier);
    //     $formArtiste->handleRequest($rq);
    //     if($formArtiste->isSubmitted() && $formArtiste->isValid()){
    //         // $em->persist($utilisateurAmodifier);
    //         $em->flush();
    //         $this->addFlash("success", "Les informations de l'artiste ont été modifiées");
    //         return $this->redirectToRoute("artist");
    //     }
    //     return $this->render("artist/form.html.twig", [ 
    //         "form" => $formArtiste->createView(), 
    //         "bouton" => "Modifier",
    //         "titre" => "Modification de l'artiste n°$id" 
    //     ]);
    // }
}
