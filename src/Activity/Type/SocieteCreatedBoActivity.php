<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\BoUserNotification;
use App\Entity\Societe;
use App\Entity\User;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteCreatedBoActivity implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    public function __construct(EntityLinkService $entityLinkService)
    {
        $this->entityLinkService = $entityLinkService;
    }

    public static function getType(): string
    {
        return 'bo_societe_created';
    }

    public static function getFilterType(): string
    {
        return 'back_office';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'societe'
        ]);

        $resolver->setAllowedTypes('societe', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s',
            '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(Societe::class, $activityParameters['societe'])
        );
    }

    public function postPersist(Societe $societe, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'societe' => intval($societe->getId()),
            ])
        ;

        $em->persist($activity);

        $boUsers = $em->getRepository(User::class)->findByRole('ROLE_BO_USER');

        foreach ($boUsers as $boUser) {
            $em->persist(BoUserNotification::create($activity, $boUser));
        }
    }
}
