<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projets;
use App\Entity\FaitsMarquants;
use App\Entity\Users;
use App\Form\ProjetFormType;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Repository\UsersRepository;
use App\Repository\ProjetsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function saisieInfosProjet(Request $rq) : Response
    {
         // On instancie l'entité "Projets" 
         $projet = new Projets();
         $statuts_projet = 1 ;
         // On crée l'objet formulaire
         $form = $this->createForm(ProjetFormType::class, $projet);

         // On récupère les données saisies, si le formulaire a été soumis
         $form->handleRequest($rq);
          
         dump($projet);
         // On vérifie si le formulaire a été envoyé et si les données sont valides
         if($form->isSubmitted() && $form->isValid()) {
      
           // On enregistre l'utilisateur en bdd
             $em = $this->getDoctrine()->getManager();
             $em->persist($projet);
             $em->flush(); // Transférer l'information vers la base de données "rdi_manager_01"

             // $request->getSession()->getFlashBag()->add();
            
             //TODO
             // $this->addFlash('info', "La fiche de l'utilisateur " . $users->getPrenom() . " " . $users->getNom() . " a été crée");
             return $this->redirectToRoute("projet");
        }
         
         return $this->render('projets/saisie_infos_projet.html.twig', [
             'form' => $form->createView(), // On créé la vue du formulaire       
        ]);
    } 

    /**
     * @Route("/fiche/projet", name="fiche_projet_")
     */
    public function ficheProjet(UsersRepository $ur)
    {
        //if ($this->getUser()) {
            //$utilisateur = $ur->find($this->getUser()->getId());
            $utilisateur = new Users;
            return $this->render('projets/fiche_projet.html.twig', [
                'utilisateur' => $utilisateur ,
            ]);    
        //dd($this);
       // }


       // return $this->redirectToRoute('fiche_projet_');
    }
}
