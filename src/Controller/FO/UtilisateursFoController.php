<?php

namespace App\Controller\FO;

use App\Entity\User;
use App\Form\InviteUserType;
use App\Form\UtilisateursFormType;
use App\Repository\UserRepository;
use App\Service\RdiMailer;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->render('utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'users' => $ur->findBySameSociete($this->getUser()),
        ]);
    }

    /**
     * @Route("/utilisateurs/invite", name="fo_user_invite")
     */
    public function invite(
        Request $request,
        EntityManagerInterface $em,
        TokenGenerator $tokenGenerator,
        RdiMailer $mailer
    ): Response {
        $user = new User();
        $user->setSociete($this->getUser()->getSociete());

        $form = $this->createForm(InviteUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setInvitationToken($tokenGenerator->generateUrlToken());

            $em->persist($user);
            $em->flush();

            $mailer->sendInvitationEmail($user, $this->getUser());

            $this->addFlash('success', sprintf('Un email avec un lien d\'invitation a été envoyé à "%s".', $user->getEmail()));

            return $this->redirectToRoute('fo_user_invite');
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

    /**
     * @Route("/utilisateur/fo/modifier/{id}", name="utilisateur_fo_modifier_", requirements={"id"="\d+"})
     */
    public function modifier(Request $rq, EntityManagerInterface $em, UserRepository $ur, $id)
    {
        return new Response('', Response::HTTP_NOT_IMPLEMENTED);

        $utilisateurAmodifier = $ur->find($id);
        $formUtilisateur = $this->createForm(UtilisateursFormType::class, $utilisateurAmodifier);
        $formUtilisateur->handleRequest($rq);
        if($formUtilisateur->isSubmitted() && $formUtilisateur->isValid()){
            // $em->persist($UtilisateurAmodifier);
            $em->flush();
            // $this->addFlash("success", "Les informations de l'Utilisateur ont été modifiées");
            return $this->redirectToRoute("utilisateurs_fo_");
        }
        return $this->render('utilisateurs_fo/infos_utilisateur_fo.html.twig', [ 
            "form" => $formUtilisateur->createView(), 
            "bouton" => "Modifier",
            "titre" => "Modification de l'utilisateur n°$id" 
        ]);
    }

    /**
     * @Route("/utilisateur/fo/supprimer/{id}", name="utilisateur_fo_supprimer_", requirements={"id"="\d+"})
     */
    public function supprimer(Request $rq, EntityManagerInterface $em, UserRepository $ur, $id)
    {
        return new Response('', Response::HTTP_NOT_IMPLEMENTED);

        $utilisateurAsupprimer = $ur->find($id);
        $formUtilisateur = $this->createForm(UtilisateursFormType::class, $utilisateurAsupprimer);
        $formUtilisateur->handleRequest($rq);
        if($formUtilisateur->isSubmitted() && $formUtilisateur->isValid()){
            $em->remove($utilisateurAsupprimer);
            $em->flush();
            $this->addFlash("success", "Les informations de l'Utilisateur ont été supprimées");
            return $this->redirectToRoute("utilisateurs_fo_");
        }
        return $this->render('utilisateurs_fo/infos_utilisateur_fo.html.twig', [ 
            "form" => $formUtilisateur->createView(), 
            "bouton" => "Confirmer",
            "titre" => "Suppression de l'utilisateur n°$id" 
        ]);
    }
}
