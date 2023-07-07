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

class FaitMarquantRestoredActivity implements ActivityInterface, EventSubscriberInterface
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
        return 'fait_marquant_restored';
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
            'restoredBy'
        ]);

        $resolver->setAllowedTypes('faitMarquant', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('restoredBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s a restauré le fait marquant %s dans le projet %s",
            '<i class="fa fa-undo" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['restoredBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FaitMarquantRestoredEvent::class => 'createActivity',
        ];
    }

    public function createActivity(FaitMarquantRestoredEvent $event): void
    {
        $faitMarquant = $event->getFaitMarquant();
        $projet = $event->getProjet();
        $createdBy = $event->getCreatedBy();
        $restoredBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'faitMarquant' => intval($faitMarquant->getId()),
                'projet' => intval($projet->getId()),
                'restoredBy' => intval($restoredBy->getId()),
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
