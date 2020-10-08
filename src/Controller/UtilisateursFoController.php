<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Form\UtilisateursFormType;




class UtilisateursFoController extends AbstractController
{
    /**
     * @Route("/utilisateurs/fo", name="utilisateurs_fo_")
     */
    public function listerUtilisateurs(UsersRepository $ur)
    {
        $liste_utilisateurs = $ur->findAll();
        return $this->render('utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'liste_utilisateurs' => $liste_utilisateurs,
        ]);
    }

    /**
     * @Route("/utilisateurs/fo/infos", name="infos_utilisateur_fo_")
     * @param Request $rq
     * @return Response
     */
    public function infosUtilisateur(Request $rq) : Response
    {
        // On instancie l'entité "Users" 
        $users = new Users();

        // On crée l'objet formulaire
        $form = $this->createForm(UtilisateursFormType::class, $users);

        // On récupère les données saisies, si le formulaire a été soumis
        $form->handleRequest($rq);

        // On vérifie si le formulaire a été envoyé et si les données sont valides
        if($form->isSubmitted() && $form->isValid()) {
            // // On enregistre l'utilisateur en bdd
            $em = $this->getDoctrine()->getManager();
            $em->persist($users);
            $em->flush(); // Transférer l'information vers la base de données "rdi_bdd_v1"

            // $request->getSession()->getFlashBag()->add();
            $this->addFlash('info', "La fiche de l'utilisateur " . $users->getPrenom() . " " . $users->getNom() . " a été crée");
            return $this->redirectToRoute("users");
        }
        
        return $this->render('utilisateurs_fo/infos_utilisateur_fo.html.twig', [
            'form' => $form->createView(), // On créé la vue du formulaire       
        ]);
    }

    /**
     * @Route("/utilisateurs/fo/compte", name="compte_")
     */
    public function compteUtilisateur()
    {
        return $this->render('utilisateurs_fo/compte_utilisateurs_fo.html.twig', [
            'controller_name' => 'UtilisateursFoController',
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
