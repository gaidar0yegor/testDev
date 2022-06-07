<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Notification\Event\SuperiorHierarchicalAddedEvent;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SuperiorHierarchicalAdded implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    public function __construct(
        EntityManagerInterface $em,
        EntityLinkService $entityLinkService
    ) {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
    }

    public static function getType(): string
    {
        return 'superior_hierarchical_added';
    }

    public static function getFilterType(): string
    {
        return 'societe_user';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'societeUser',
            'superior'
        ]);

        $resolver->setAllowedTypes('societeUser', 'integer');
        $resolver->setAllowedTypes('superior', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s a désigné %s en tant que son supérieur hiérarchique (N+1).",
            '<i class="fa fa-user" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['societeUser']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['superior'])
         );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SuperiorHierarchicalAddedEvent::class => 'createActivity',
        ];
    }

    public function createActivity(SuperiorHierarchicalAddedEvent $event): void
    {
        $societeUser = $event->getSocieteUser();

        if ($event->hasSuperior()){
            $activity = new Activity();
            $activity
                ->setType(self::getType())
                ->setParameters([
                    'societeUser' => intval($societeUser->getId()),
                    'superior' => intval($societeUser->getMySuperior()->getId())
                ])
            ;

            $societeUserNotification = SocieteUserNotification::create($activity,$societeUser->getMySuperior());

            $societeUserActivity = new SocieteUserActivity();
            $societeUserActivity
                ->setSocieteUser($societeUser)
                ->setActivity($activity)
            ;

            $this->em->persist($activity);
            $this->em->persist($societeUserActivity);
            $this->em->persist($societeUserNotification);
        }
    }
}
