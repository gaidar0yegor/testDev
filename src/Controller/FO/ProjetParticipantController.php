<?php

namespace App\Controller\FO;

use App\DTO\InvitationUserSurProjet;
use App\Entity\Projet;
use App\Form\InviteUserSurProjetType;
use App\Form\ListeProjetParticipantsType;
use App\Service\Invitator;
use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjetParticipantController extends AbstractController
{
    /**
     * @Route("/projets/{id}/participants", name="app_fo_projet_participant")
     */
    public function index(
        Request $request,
        Projet $projet,
        SocieteChecker $societeChecker,
        UserContext $userContext,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        $form = $this->createForm(ListeProjetParticipantsType::class, $projet);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Empêche de faire basculer le projet dans une autre société
            if (!$societeChecker->isSameSociete($projet, $userContext->getSocieteUser())) {
                throw $this->createAccessDeniedException();
            }

            $em->flush();

            $this->addFlash('success', 'Les rôles des participants ont été mis à jour.');

            return $this->redirectToRoute('app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/participants/index.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/projets/{id}/participants/invite", name="app_fo_projet_participant_invite")
     */
    public function invite(
        Request $request,
        Projet $projet,
        Invitator $invitator,
        UserContext $userContext,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        $societeUser = $invitator->initUser($userContext->getSocieteUser()->getSociete());
        $invitation = new InvitationUserSurProjet();
        $form = $this->createForm(InviteUserSurProjetType::class, $invitation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societeUser->setInvitationEmail($invitation->getEmail());
            $invitator->addParticipation($societeUser, $projet, $invitation->getRole());

            $invitator->check($societeUser);
            $invitator->sendInvitation($societeUser, $this->getUser());
            $em->flush();

            $this->addFlash('success', sprintf(
                'Un email avec un lien d\'invitation a été envoyé à "%s".',
                $invitation->getEmail()
            ));

            return $this->redirectToRoute('app_fo_projet_participant_invite', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/participants/invite.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }
}
