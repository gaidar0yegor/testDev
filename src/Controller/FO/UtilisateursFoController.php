<?php

namespace App\Controller\FO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UtilisateursFormType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_FO_ADMIN")
 */
class UtilisateursFoController extends AbstractController
{
    /**
     * @Route("/utilisateurs/fo", name="utilisateurs_fo_")
     */
    public function listerUtilisateurs(UserRepository $ur)
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
    public function infosUtilisateur(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();

        // On crée l'objet formulaire
        $form = $this->createForm(UtilisateursFormType::class, $user);

        // On récupère les données saisies, si le formulaire a été soumis
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user
                ->setSociete($this->getUser()->getSociete())
            ;

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

            return $this->redirectToRoute('utilisateurs_fo_');
        }

        return $this->render('utilisateurs_fo/infos_utilisateur_fo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/utilisateurs/fo/compte", name="compte_")
     */
    public function compteUtilisateur()
    {
        // if (!$this->getUser()) {
        //     return $this->redirectToRoute('compte_');
        // }

        return $this->render('utilisateurs_fo/compte_utilisateurs_fo.html.twig', [
            'utilisateur' => $this->getUser(),
        ]);
    }

    // /**
    //  * @Route("/utilisateurs/fo/modifier/{id}", name="utilisateurs_fo_modifier", requirements={"id"="\d+"})
    //  */
    // public function modifier(Request $rq, EntityManager $em, UsersRepository $ur, $id)
    // {
    //     // Modifier un utilisateur existant
    //     // récupérer l'utilisateur avec son id, l'afficher dans le formulaire et enregistrer les modifications dans la base de données
    //     $utilisateurAmodifier = $ur->find($id);
    //     $formUtilisateur = $this->createForm(UserType::class, $utilisateurAmodifier);
    //     $formUtilisateur->handleRequest($rq);
    //     if($formUtilisateur->isSubmitted() && $formUtilisateur->isValid()){
    //         // $em->persist($utilisateurAmodifier);
    //         $em->flush();
    //         $this->addFlash("success", "Les informations de l'utilisateur ont été modifiées");
    //         return $this->redirectToRoute("compte_");
    //     }
    //     return $this->render("utilisateurs_fo/compte_utilisateurs_fo.html.twig", [ 
    //         "form" => $formUtilisateur->createView(), 
    //         // "bouton" => "Modifier",
    //         // "titre" => "Modification de l'artiste n°$id" 
    //     ]);
    // }
}
