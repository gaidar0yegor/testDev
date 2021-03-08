<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class FaitMarquantModifiedActivity implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    private Security $security;

    public function __construct(EntityLinkService $entityLinkService, Security $security)
    {
        $this->entityLinkService = $entityLinkService;
        $this->security = $security;
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
                $this->entityLinkService->generateLink(User::class, $activityParameters['modifiedBy']),
                $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
                $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            );
        }

        return sprintf(
            '%s %s a modifié le fait marquant %s créé par %s sur le projet %s.',
            '<i class="fa fa-edit" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['modifiedBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(User::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postUpdate(FaitMarquant $faitMarquant, LifecycleEventArgs $args): ?Activity
    {
        $modifiedBy = $this->security->getUser();

        if (!$modifiedBy instanceof User) {
            throw new RuntimeException('Impossible to get current user to determine who modified FaitMarquant');
        }

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

        $userActivity = new UserActivity();
        $userActivity
            ->setUser($modifiedBy)
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($faitMarquant->getProjet())
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();

        $em->persist($activity);
        $em->persist($userActivity);
        $em->persist($projetActivity);
        $em->flush();

        return $activity;
    }
}
