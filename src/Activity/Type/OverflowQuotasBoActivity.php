<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\BoUserNotification;
use App\Entity\Societe;
use App\Entity\User;
use App\Notification\Event\OverflowQuotasBoNotification;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OverflowQuotasBoActivity implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    public function __construct(
        EntityLinkService $entityLinkService,
        EntityManagerInterface $em
    ) {
        $this->entityLinkService = $entityLinkService;
        $this->em = $em;
    }

    public static function getType(): string
    {
        return 'bo_overflow_quotas';
    }

    public static function getFilterType(): string
    {
        return 'back_office';
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s | %s',
            '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(Societe::class, $activityParameters['societe']),
            $activityParameters['limitedElement']
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'societe',
            'limitedElement',
        ]);

        $resolver->setAllowedTypes('societe', 'integer');
        $resolver->setAllowedTypes('limitedElement', 'string');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OverflowQuotasBoNotification::class => 'onNotification',
        ];
    }

    public function onNotification(OverflowQuotasBoNotification $event): void
    {
        $this->em->clear();

        $activity = new Activity();

        $activity
            ->setType(self::getType())
            ->setParameters([
                'societe' => $event->getSociete()->getId(),
                'limitedElement' => $event->getLimitedElement(),
            ])
        ;

        $this->em->persist($activity);

        $boUsers = $this->em->getRepository(User::class)->findByRole('ROLE_BO_USER');

        foreach ($boUsers as $boUser) {
            $this->em->persist(BoUserNotification::create($activity, $boUser));
        }

        $this->em->flush();
    }
}
