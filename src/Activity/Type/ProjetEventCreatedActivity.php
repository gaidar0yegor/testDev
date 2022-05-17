<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetEvent;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserNotification;
use App\MultiSociete\UserContext;
use App\Service\EntityLink\EntityLinkService;
use App\Service\ParticipantService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetEventCreatedActivity implements ActivityInterface
{
    private EntityLinkService $entityLinkService;
    private UserContext $userContext;

    public function __construct(EntityLinkService $entityLinkService, UserContext $userContext)
    {
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
    }

    public static function getType(): string
    {
        return 'projet_event_created';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'projetEvent',
            'createdBy',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('projetEvent', 'integer');
        $resolver->setAllowedTypes('createdBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a créé l\'évènement %s sur le projet %s.',
            '<i class="fa fa-calendar" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(ProjetEvent::class, $activityParameters['projetEvent']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postPersist(ProjetEvent $projetEvent, LifecycleEventArgs $args): void
    {
        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($projetEvent->getProjet()->getId()),
                'projetEvent' => intval($projetEvent->getId()),
                'createdBy' => intval($projetEvent->getCreatedBy()->getId()),
            ])
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($projetEvent->getProjet())
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();

        foreach ($projetEvent->getProjetEventParticipants() as $projetEventParticipant) {
            $societeUser = $projetEventParticipant->getParticipant()->getSocieteUser();
            if ($societeUser !== $this->userContext->getSocieteUser()){
                $em->persist(SocieteUserNotification::create($activity, $societeUser));
            }
        }

        $em->persist($activity);
        $em->persist($projetActivity);
        $em->flush();
    }
}
