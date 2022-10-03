<?php

namespace App\Controller\CorpApp\FO\Admin;

use App\Form\InviteUserType;
use App\Repository\SocieteUserRepository;
use App\Service\Invitator;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/utilisateurs")
 */
class SocieteUserController extends AbstractController
{
    /**
     * @Route("", name="corp_app_fo_admin_utilisateurs")
     */
    public function listerUtilisateurs(SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        return $this->render('corp_app/utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'societeUsers' => $societeUserRepository->findBySameSociete($userContext->getSocieteUser()),
        ]);
    }

    /**
     * @Route("/invite", name="corp_app_fo_admin_user_invite")
     */
    public function invite(
        Request $request,
        UserContext $userContext,
        EntityManagerInterface $em,
        Invitator $invitator
    ): Response {
        $societeUser = $invitator->initUser($userContext->getSocieteUser()->getSociete());
        $form = $this->createForm(InviteUserType::class, $societeUser);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitator->check($societeUser);
            $invitator->sendInvitation($societeUser, $this->getUser());
            $em->flush();

            $this->addFlash('success', 'Un lien d\'invitation a été envoyé.');

            return $this->redirectToRoute('corp_app_fo_admin_user_invite');
        }

        return $this->render('corp_app/utilisateurs_fo/invite_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
