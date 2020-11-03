<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\SocieteStatut;
use App\Repository\UserRepository;
use App\Form\UtilisateursFormType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// use App\Repository\SocieteStatutRepository;



class UtilisateursBoController extends AbstractController
{
    /**
     * @Route("/utilisateurs/bo", name="utilisateurs_bo_")
     */
    public function gererUtilisateursBo(UserRepository $ur)
    {
        $liste_utilisateurs = $ur->findAll();
        return $this->render('utilisateurs_bo/liste_utilisateurs_bo.html.twig', [
            'liste_utilisateurs' => $liste_utilisateurs,
        ]);
    }

    /**
     * @Route("/infos/utilisateur/bo", name="infos_utilisateur_bo_")
     */
    public function infosUtilisateurBo(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();

        // On crée l'objet formulaire
        $form = $this->createForm(UtilisateursFormType::class, $user);

        // On récupère les données saisies, si le formulaire a été soumis
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            /*
            // Need password now ?
            $user->setPassword(
                $passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData())
            );
            */
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('info', sprintf('La fiche de l\'utilisateur %s %s a été crée.', $user->getPrenom(), $user->getNom()));
            return $this->redirectToRoute('utilisateurs_bo_');
        }

        return $this->render('utilisateurs_bo/infos_utilisateur_bo.html.twig', [
            'form' => $form->createView(),     
            "bouton" => "Ajouter",

            // 'controller_name' => 'UtilisateursBoController',
        ]);
    }


    /**
     * @Route("/utilisateur/modifier/{id}", name="utilisateur_modifier_", requirements={"id"="\d+"})
     */
    public function modifier(Request $rq, EntityManager $em, UserRepository $ur, $id)
    {
        $utilisateurAmodifier = $ur->find($id);
        $formUtilisateur = $this->createForm(UtilisateursFormType::class, $utilisateurAmodifier);
        $formUtilisateur->handleRequest($rq);
        if($formUtilisateur->isSubmitted() && $formUtilisateur->isValid()){
            // $em->persist($UtilisateurAmodifier);
            $em->flush();
            // $this->addFlash("success", "Les informations de l'Utilisateur ont été modifiées");
            return $this->redirectToRoute("utilisateurs_bo_");
        }
        return $this->render('utilisateurs_bo/infos_utilisateur_bo.html.twig', [ 
            "form" => $formUtilisateur->createView(), 
            "bouton" => "Modifier",
            "titre" => "Modification de l'utilisateur n°$id" 
        ]);
    }

    /**
     * @Route("/utilisateur/supprimer/{id}", name="utilisateur_supprimer_", requirements={"id"="\d+"})
     */
    public function supprimer(Request $rq, EntityManager $em, UserRepository $ur, $id)
    {
        $utilisateurAsupprimer = $ur->find($id);
        $formUtilisateur = $this->createForm(UtilisateursFormType::class, $utilisateurAsupprimer);
        $formUtilisateur->handleRequest($rq);
        if($formUtilisateur->isSubmitted() && $formUtilisateur->isValid()){
            $em->remove($utilisateurAsupprimer);
            $em->flush();
            // $this->addFlash("success", "Les informations de l'Utilisateur ont été supprimées");
            return $this->redirectToRoute("utilisateurs_bo_");
        }
        return $this->render('utilisateurs_bo/infos_utilisateur_bo.html.twig', [ 
            "form" => $formUtilisateur->createView(), 
            "bouton" => "Confirmer",
            "titre" => "Suppression de l'utilisateur n°$id" 
        ]);
    }

