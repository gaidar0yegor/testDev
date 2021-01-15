<?php

namespace App\Activity\Type;

use App\Activity\ActivityEvent;
use App\Activity\ActivityHandlerInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaitMarquantCreatedActivity implements ActivityHandlerInterface
{
    public const TYPE = 'fait_marquant_created';

    private EntityLinkService $entityLinkService;

    public function __construct(EntityLinkService $entityLinkService)
    {
        $this->entityLinkService = $entityLinkService;
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'createdBy',
            'faitMarquant',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('createdBy', 'integer');
        $resolver->setAllowedTypes('faitMarquant', 'integer');
    }

    public function render(array $activityParameters): string
    {
        return sprintf(
            '%s %s a ajouté le fait marquant %s sur le projet %s.',
            '<i class="fa fa-map-marker" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function getSubscribedEvent(): array
    {
        return [FaitMarquant::class, ActivityEvent::CREATED];
    }

    /**
     * @param FaitMarquant $faitMarquant
     */
    public function onEvent($faitMarquant, EntityManagerInterface $em): ?Activity
    {
        $activity = new Activity();
        $activity
            ->setType(self::TYPE)
            ->setParameters([
                'projet' => intval($faitMarquant->getProjet()->getId()),
                'createdBy' => intval($faitMarquant->getCreatedBy()->getId()),
                'faitMarquant' => intval($faitMarquant->getId()),
            ])
        ;

        $userActivity = new UserActivity();
        $userActivity
            ->setUser($faitMarquant->getCreatedBy())
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($faitMarquant->getProjet())
            ->setActivity($activity)
        ;

        $em->persist($activity);
        $em->persist($userActivity);
        $em->persist($projetActivity);
        $em->flush();

        return $activity;
    }
}
