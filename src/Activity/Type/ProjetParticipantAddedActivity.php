<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Notification\Event\ProjetParticipantAddedEvent;
use App\Security\Role\RoleProjet;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjetParticipantAddedActivity implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    private UrlGeneratorInterface $urlGenerator;

    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        EntityLinkService $entityLinkService,
        UserContext $userContext,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }


    public static function getType(): string
    {
        return 'projet_participant_added';
    }

    public static function getFilterType(): string
    {
        return 'projet';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'participant',
            'role',
            'addedBy',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('participant', 'integer');
        $resolver->setAllowedTypes('role', 'string');
        $resolver->setAllowedTypes('addedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s a rejoint le projet %s en tant que %s, invité par %s.",
            '<i class="fa fa-user-plus" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['participant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet']),
            $this->translator->trans($activityParameters['role']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['addedBy'])
         );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjetParticipantAddedEvent::class => 'createActivity',
        ];
    }

    public function createActivity(ProjetParticipantAddedEvent $event): void
    {
        $projetParticipant = $event->getProjetParticipant();
        $projet = $event->getProjet();
        $participant = $event->getSocieteUser();
        $addedBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($projet->getId()),
                'participant' => intval($participant->getId()),
                'role' => $projetParticipant->getRole(),
                'addedBy' => intval($addedBy->getId()),
            ])
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setActivity($activity)
            ->setProjet($projet)
        ;

        $societeUserNotification = SocieteUserNotification::create($activity,$participant);

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($participant)
            ->setActivity($activity)
        ;

        $this->em->persist($activity);
        $this->em->persist($projetActivity);
        $this->em->persist($societeUserActivity);
        $this->em->persist($societeUserNotification);
        $this->addAccessesFichierProjet($projet, $projetParticipant);
    }

    private function addAccessesFichierProjet(Projet $projet, ProjetParticipant $projetParticipant)
    {
        foreach ($projet->getFichierProjets() as $fichierProjet) {
            $accessChoices = $fichierProjet->getAccessesChoices();
            if (
                empty($accessChoices) || in_array('all', $accessChoices) ||
                (in_array(RoleProjet::CDP, $accessChoices) && RoleProjet::CDP === $projetParticipant->getRole()) ||
                (in_array(RoleProjet::CONTRIBUTEUR, $accessChoices) && RoleProjet::CONTRIBUTEUR === $projetParticipant->getRole()) ||
                (in_array(RoleProjet::OBSERVATEUR, $accessChoices) && RoleProjet::OBSERVATEUR === $projetParticipant->getRole())
            ) {
                $fichierProjet->addSocieteUser($projetParticipant->getSocieteUser());
                $this->em->persist($fichierProjet);
            }
        }
    }
}
