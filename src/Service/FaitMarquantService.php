<?php

namespace App\Service;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetObservateurExterne;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\User;
use App\MultiSociete\UserContext;
use App\Notification\Event\ProjetParticipantAddedEvent;
use App\ObservateurExterne\InvitationService;
use App\Security\Role\RoleProjet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FaitMarquantService
{
    private EntityManagerInterface $em;
    private UserContext $userContext;
    private InvitationService $externeInvitationService;
    private Invitator $invitationService;
    private AuthorizationCheckerInterface $authChecker;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        EntityManagerInterface $em,
        UserContext $userContext,
        InvitationService $externeInvitationService,
        Invitator $invitationService,
        AuthorizationCheckerInterface $authChecker,
        EventDispatcherInterface $dispatcher
    )
    {
        $this->em = $em;
        $this->userContext = $userContext;
        $this->externeInvitationService = $externeInvitationService;
        $this->invitationService = $invitationService;
        $this->authChecker = $authChecker;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Créer un fait marquant de suspension de projet
     */
    public function CreateFmOfProjectSuspension(Projet $projet): FaitMarquant
    {
        $faitMarquant = new FaitMarquant();

        $faitMarquant->setTitre('Projet suspendu');
        $faitMarquant->setDescription('Suspension temporaire du projet pour des raisons stratégiques internes. Plus aucune action (saisie de faits marquants, suivi du temps, …) ne sera possible sur ce projet jusqu’à sa réactivation. Seule la consultation de la page projet sera possible durant toute la période de suspension. Pour toute information complémentaire, veuillez contacter le responsable des projets.');
        $faitMarquant->setDate(new \DateTime());

        $faitMarquant->setProjet($projet);
        $faitMarquant->setCreatedBy($this->userContext->getSocieteUser());

        return $faitMarquant;
    }

    /**
     * Créer un fait marquant de réactivation de projet
     */
    public function CreateFmOfProjectResume(Projet $projet): FaitMarquant
    {
        $faitMarquant = new FaitMarquant();

        $faitMarquant->setTitre('Projet réactivé');
        $faitMarquant->setDescription('Réactivation du projet après validation en interne avec les principales parties prenantes. Les contributeurs du projet peuvent à nouveau réaliser des actions (saisie de faits marquants, suivi du temps, …) sur ce projet. Pour toute information complémentaire, veuillez contacter le responsable des projets.');
        $faitMarquant->setDate(new \DateTime());

        $faitMarquant->setProjet($projet);
        $faitMarquant->setCreatedBy($this->userContext->getSocieteUser());

        return $faitMarquant;
    }

    /**
     * inviter users tagués sur un fait marquant
     */
    public function inviteUserTaggedSurFm(Projet $projet, string $email) : array
    {
        $invitationSended = false;
        $sendFm = false;
        $canInvite = $this->authChecker->isGranted('edit', $projet);

        if ($canInvite){
            $user = $this->em->getRepository(User::class)->findByEmailAndSociete($projet->getSociete(), $email);

            if ($user instanceof User){
                $sendFm = true;
                $projetParticipant = $this->em->getRepository(ProjetParticipant::class)->findByUserAndProjet($user,$projet);

                if ($projetParticipant === null){
                    $invitationSended = true;
                    $user->getSocieteUsers()->map(function($societeUser) use ($projet){
                        if ($societeUser->getSociete()->getId() === $projet->getSociete()->getId()){
                            $projetParticipant = $this->invitationService->addParticipation($societeUser, $projet, RoleProjet::OBSERVATEUR);
                            $this->em->flush();
                            $this->dispatcher->dispatch(new ProjetParticipantAddedEvent($projetParticipant));
                        }
                        return;
                    });
                }
            } else {
                $exUser = $this->em->getRepository(User::class)->findExterneByEmailAndProjet($projet, $email);
                if ($exUser === null){
                    $exInvitedUser = $this->em->getRepository(ProjetObservateurExterne::class)->findOneBy(['projet' => $projet , 'invitationEmail' => $email]);
                    $invitedSocieteUser = $this->em->getRepository(SocieteUser::class)->findOneBy(['societe' => $projet->getSociete(), 'invitationEmail' => $email]);
                    if ($exInvitedUser === null && $invitedSocieteUser === null){
                        $invitationSended = true;
                        $this->externeInvitationService->sendAutomaticInvitationSurProjet($projet,$email);
                    }
                }
            }

            $this->em->flush();
        }

        return [
            'invitationSended' => $invitationSended,
            'sendFm' => $sendFm,
        ];
    }
}
