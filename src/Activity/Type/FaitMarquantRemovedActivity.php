<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Notification\Event\FaitMarquantRemovedEvent;
use App\Notification\Event\FaitMarquantRestoredEvent;
use App\Notification\Event\ProjetParticipantRemovedEvent;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FaitMarquantRemovedActivity implements ActivityInterface, EventSubscriberInterface
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
        return 'fait_marquant_removed';
    }

    public static function getFilterType(): string
    {
        return 'fait_marquant';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'faitMarquant',
            'projet',
            'createdBy',
            'removedBy',
        ]);

        $resolver->setAllowedTypes('faitMarquant', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('createdBy', 'integer');
        $resolver->setAllowedTypes('removedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s a supprimé le fait marquant %s, créé par %s, du projet %s",
            '<i class="fa fa-trash" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['removedBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FaitMarquantRemovedEvent::class => 'createActivity',
        ];
    }

    public function createActivity(FaitMarquantRemovedEvent $event): void
    {
        $faitMarquant = $event->getFaitMarquant();
        $projet = $event->getProjet();
        $createdBy = $event->getCreatedBy();
        $removedBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'faitMarquant' => intval($faitMarquant->getId()),
                'projet' => intval($projet->getId()),
                'createdBy' => intval($createdBy->getId()),
                'removedBy' => intval($removedBy->getId()),
            ])
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setActivity($activity)
            ->setProjet($projet)
        ;

        $societeUserNotification = SocieteUserNotification::create($activity,$createdBy);

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($createdBy)
            ->setActivity($activity)
        ;

        $this->em->persist($activity);
        $this->em->persist($projetActivity);
        $this->em->persist($societeUserActivity);
        $this->em->persist($societeUserNotification);

        $this->em->flush();
    }
}
