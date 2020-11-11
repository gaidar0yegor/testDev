<?php

namespace App\Controller\FO;

use App\DTO\InvitationUserSurProjet;
use App\Entity\Projet;
use App\Form\InviteUserSurProjetType;
use App\Form\ListeProjetParticipantsType;
use App\Service\Invitator;
use App\Service\SocieteChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjetParticipantController extends AbstractController
{
    /**
     * @Route("/projet/{id}/participants", name="projet_participant")
     */
    public function index(
        Request $request,
        Projet $projet,
        SocieteChecker $societeChecker,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        $form = $this->createForm(ListeProjetParticipantsType::class, $projet, [
            'societe' => $projet->getSociete(),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Empêche de faire basculer le projet dans une autre société
            if (!$societeChecker->isSameSociete($projet->getChefDeProjet(), $this->getUser())) {
                throw $this->createAccessDeniedException();
            }

            $em->flush();

            $this->addFlash('success', 'Les rôles des participants ont été mis à jour.');

            return $this->redirectToRoute('fiche_projet_', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/participants/index.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/projet/{id}/participants/invite", name="projet_participant_invite")
     */
    public function invite(
        Request $request,
        Projet $projet,
        Invitator $invitator,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        $user = $invitator->initUser();
        $invitation = new InvitationUserSurProjet();
        $form = $this->createForm(InviteUserSurProjetType::class, $invitation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($invitation->getEmail());
            $invitator->addParticipation($user, $projet, $invitation->getRole());

            $invitator->check($user);
            $em->flush();
            $invitator->sendInvitation($user);

            return $this->redirectToRoute('projet_participant_invite', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/participants/invite.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }
}
