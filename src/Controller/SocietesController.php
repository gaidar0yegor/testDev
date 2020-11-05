<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Societe;
use App\Repository\SocieteRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\SocieteFormType;
// use App\Entity\Licences;    
// use App\Entity\SocieteStatut;
// use App\Repository\SocieteStatutRepository;


class SocietesController extends AbstractController
{
    /**
     * @Route("/societes", name="societes_")
     */
    public function listeSocietes(SocieteRepository $sr)
    {
        $liste_societes = $sr->findAll();
        return $this->render('societes/liste_societes.html.twig', [
            'liste_societes' => $liste_societes,
            // 'controller_name' => 'SocietesController',
        ]);
    }

    /**
     * @Route("/infos_societe", name="infos_societe_")
     * @param Request $rq
     * @return Response
     */
    public function saisieInfosSociete(Request $request, EntityManager $em): Response
    {
        $societe = new Societe();

        $form = $this->createForm(SocieteFormType::class, $societe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($societe);
            $em->flush();            
            
            $this->addFlash('info', sprintf('La fiche de la société %s été crée.', $societe->getRaisonSociale()) );
            return $this->redirectToRoute('societes_');
        }

        return $this->render('societes/saisie_infos_societe.html.twig', [
            'societe' => $societe,
            "bouton" => "Ajouter",
            'form' => $form->createView(),
            // 'controller_name' => 'SocietesController',
        ]);
    }

    /**
     * @Route("/societes/modifier/{id}", name="societes_modifier_", requirements={"id"="\d+"})
     */
    public function modifier(Request $rq, EntityManager $em, SocieteRepository $sr, $id)
    {
        $societeAmodifier = $sr->find($id);
        $formSociete = $this->createForm(SocieteFormType::class, $societeAmodifier);
        $formSociete->handleRequest($rq);
        if($formSociete->isSubmitted() && $formSociete->isValid()){
            // $em->persist($UtilisateurAmodifier);
            $em->flush();
            // $this->addFlash("success", "Les informations de l'Utilisateur ont été modifiées");
            return $this->redirectToRoute("societes_");
        }
        return $this->render('societes/saisie_infos_societe.html.twig', [
            "form" => $formSociete->createView(), 
            "bouton" => "Modifier",
            "titre" => "Modification de la société n°$id" 
        ]);
    }

    /**
     * @Route("/societes/supprimer/{id}", name="societes_supprimer_", requirements={"id"="\d+"})
     */
    public function supprimer(Request $rq, EntityManager $em, SocieteRepository $sr, $id)
    {
        $societeAsupprimer = $sr->find($id);
        $formSociete = $this->createForm(SocieteFormType::class, $societeAsupprimer);
        $formSociete->handleRequest($rq);
        if($formSociete->isSubmitted() && $formSociete->isValid()){
            $em->remove($societeAsupprimer);
            // $em->persist($UtilisateurAsupprimer$societeAsupprimer);
            $em->flush();
            // $this->addFlash("success", "Les informations de l'Utilisateur ont été modifiées");
            return $this->redirectToRoute("societes_");
        }
        return $this->render('societes/saisie_infos_societe.html.twig', [
            "form" => $formSociete->createView(), 
            "bouton" => "Confirmer",
            "titre" => "Suppression de la société n°$id" 
        ]);
    }

}
