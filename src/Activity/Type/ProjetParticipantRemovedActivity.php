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
use App\Notification\Event\ProjetParticipantRemovedEvent;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjetParticipantRemovedActivity implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $em,
        EntityLinkService $entityLinkService,
        UserContext $userContext,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
        $this->urlGenerator = $urlGenerator;
    }


    public static function getType(): string
    {
        return 'projet_participant_removed';
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
            'removedBy',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('participant', 'integer');
        $resolver->setAllowedTypes('removedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s n'est plus sur le projet %s, retiré par %s.",
            '<i class="fa fa-trash" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['participant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['removedBy'])
         );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjetParticipantRemovedEvent::class => 'createActivity',
        ];
    }

    public function createActivity(ProjetParticipantRemovedEvent $event): void
    {
        $projet = $event->getProjet();
        $participant = $event->getSocieteUser();
        $removedBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($projet->getId()),
                'participant' => intval($participant->getId()),
                'removedBy' => intval($removedBy->getId()),
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
    }
}
