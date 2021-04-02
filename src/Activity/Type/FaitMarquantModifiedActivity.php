<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaitMarquantModifiedActivity implements ActivityInterface
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
        return 'fait_marquant_modified';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'createdBy',
            'modifiedBy',
            'faitMarquant',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('createdByBy', 'integer');
        $resolver->setAllowedTypes('modifiedBy', 'integer');
        $resolver->setAllowedTypes('faitMarquant', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        if ($activityParameters['createdBy'] === $activityParameters['modifiedBy']) {
            return sprintf(
                '%s %s a modifié son fait marquant %s sur le projet %s.',
                '<i class="fa fa-edit" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
                $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
                $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            );
        }

        return sprintf(
            '%s %s a modifié le fait marquant %s créé par %s sur le projet %s.',
            '<i class="fa fa-edit" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postUpdate(FaitMarquant $faitMarquant, LifecycleEventArgs $args): ?Activity
    {
        $modifiedBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($faitMarquant->getProjet()->getId()),
                'createdBy' => intval($faitMarquant->getCreatedBy()->getId()),
                'modifiedBy' => intval($modifiedBy->getId()),
                'faitMarquant' => intval($faitMarquant->getId()),
            ])
        ;

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($modifiedBy)
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($faitMarquant->getProjet())
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();

        if ($faitMarquant->getCreatedBy() !== $modifiedBy) {
            $societeUserNotification = SocieteUserNotification::create($activity, $faitMarquant->getCreatedBy());
            $em->persist($societeUserNotification);
        }

        $em->persist($activity);
        $em->persist($societeUserActivity);
        $em->persist($projetActivity);
        $em->flush();

        return $activity;
    }
}
