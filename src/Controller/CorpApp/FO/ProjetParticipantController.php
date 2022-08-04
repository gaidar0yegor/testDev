<?php

namespace App\Controller\CorpApp\FO;

use App\DTO\InvitationUserSurProjet;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Form\InviteUserSurProjetType;
use App\Form\ListeProjetParticipantsType;
use App\Notification\Event\ProjetParticipantAddedEvent;
use App\Notification\Event\ProjetParticipantRemovedEvent;
use App\Service\Invitator;
use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProjetParticipantController extends AbstractController
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("/projets/{id}/participants", name="corp_app_fo_projet_participant")
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

        $oldProjetParticipantIds = $projet->getProjetParticipants()->map(function($obj){ return $obj->getId(); })->getValues();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Empêche de faire basculer le projet dans une autre société
            if (!$societeChecker->isSameSociete($projet, $userContext->getSocieteUser())) {
                throw $this->createAccessDeniedException();
            }

            // START:: Un évenement pour créer une activité lors de la suppression d'un ProjetParticipant
            $newProjetParticipantIds = $projet->getProjetParticipants()->map(function($obj){ return $obj->getId(); })->getValues();
            $removedProjetParticipantIds = array_diff($oldProjetParticipantIds, $newProjetParticipantIds);

            foreach ($removedProjetParticipantIds as $removedProjetParticipantId){
                $this->dispatcher->dispatch(new ProjetParticipantRemovedEvent($em->getRepository(ProjetParticipant::class)->find($removedProjetParticipantId)));
            }
            // END

            $em->flush();

            // START:: Un évenement pour créer une activité lors de la ajout d'un ProjetParticipant
            $newProjetParticipantIds = $projet->getProjetParticipants()->map(function($obj){ return $obj->getId(); })->getValues();
            $addedProjetParticipantIds = array_diff($newProjetParticipantIds, $oldProjetParticipantIds);

            foreach ($addedProjetParticipantIds as $addedProjetParticipantId){
                $this->dispatcher->dispatch(new ProjetParticipantAddedEvent($em->getRepository(ProjetParticipant::class)->find($addedProjetParticipantId)));
            }
            // END

            $em->flush();

            $this->addFlash('success', 'Les rôles des participants ont été mis à jour.');

            return $this->redirectToRoute('corp_app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/projets/participants/index.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/projets/{id}/participants/invite", name="corp_app_fo_projet_participant_invite")
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

            return $this->redirectToRoute('corp_app_fo_projet_participant_invite', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/projets/participants/invite.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }
}
