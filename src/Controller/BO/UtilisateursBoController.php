<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            // 'controller_name' => 'UtilisateursBoController',
        ]);
    }

    // /**
    // * @Route("/infos/utilisateur/bo/supprimer", name="infos_utilisateur_bo_supprimer_")
    // */
    // public function supprimer(Request $rq, EntityManager $em, UserRepository $ur)
    // {
    //     $userDelete = $ur->find($id);
    //     $formUtilisateur = $this->createForm(UtilisateurFormType::class, $userDelete);
    //     $formUtilisateur->handleRequest($rq);
    //     if($formUtilisateur->isSubmitted() && $formUtilisateur->isValid()){
    //         $em->remove($userDelete);
    //         $em->flush();
    //         $this->addFlash("success", "L'artiste " . $artisteAsupprimer->getName() . " a été supprimé");
    //         return $this->redirectToRoute("utilisateurs_bo_");
    //     }
    //     $this->addFlash("warning", "<strong>Confirmation</strong> de suppresion");
    //     return $this->render("artist/form.html.twig", [ 
    //         "form" => $formUtilisateur->createView(), 
    //         "bouton" => "Confirmer",
    //         // "titre" => "Suppression de l'artiste n°$id"
    //     ]);
    // }
}
