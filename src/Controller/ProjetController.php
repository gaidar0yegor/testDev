<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projet;
use App\Entity\User;
use App\Form\ProjetFormType;
use App\Repository\ProjetRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjetController extends AbstractController
{
    /**
     * @Route("/projets", name="projets_")
     */
    public function listerProjet(ProjetRepository $projetRepository)
    {
        return $this->render('projets/liste_projets.html.twig', [
            'liste_projets'=> $projetRepository->findAll(),
        ]);

    }

    /**
     * @Route("/infos_projet", name="infos_projet_")
     */
    public function saisieInfosProjet(Request $rq) : Response
    {
         $projet = new Projet();
         $form = $this->createForm(ProjetFormType::class, $projet);

         $form->handleRequest($rq);
          
         if($form->isSubmitted() && $form->isValid()) {
             $em = $this->getDoctrine()->getManager();
             $em->persist($projet);
             $em->flush();
            
             $this->addFlash('success', sprintf('Le projet "%s" a été créé.', $projet->getTitre()));
             return $this->redirectToRoute('projets_');
        }
         
         return $this->render('projets/saisie_infos_projet.html.twig', [
             'form' => $form->createView(), // On créé la vue du formulaire       
        ]);
    } 

    /**
     * @Route("/fiche/projet/{id}", name="fiche_projet_")
     */
    public function ficheProjet(Projet $projet)
    {
        return $this->render('projets/fiche_projet.html.twig', [
            'projet' => $projet,
        ]);
    }
}
