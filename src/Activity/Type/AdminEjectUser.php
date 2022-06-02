<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEjectUser implements ActivityInterface
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
        return 'admin_eject_user';
    }

    public static function getFilterType(): string
    {
        return 'societe_user';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'user',
            'modifiedBy',
            'userEnabled',
        ]);

        $resolver->setAllowedTypes('user', 'integer');
        $resolver->setAllowedTypes('modifiedBy', 'integer');
        $resolver->setAllowedTypes('userEnabled', 'boolean');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        if($activityParameters['userEnabled'] === true) {
            return sprintf(
                "%s %s a ré-activé le compte de %s",
                '<i class="fa fa-check-circle-o" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['user'])
        );}

        return sprintf(
            "%s %s a desactivé le compte de %s",
            '<i class="fa fa-ban" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['user'])
         );
    }

    public function postUpdate(SocieteUser $societeUser, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($societeUser);

        if (!isset($changes['enabled'])) {
            return;
        }

        $modifiedBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'user' => intval($societeUser->getId()),
                'modifiedBy' => intval($modifiedBy->getId()),
                'userEnabled' => $changes['enabled'][1],
            ])
        ;

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($societeUser)
            ->setActivity($activity)
        ;

        $adminActivity = new SocieteUserActivity();
        $adminActivity
            ->setSocieteUser($modifiedBy)
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();
        $em->persist($activity);
        $em->persist($societeUserActivity);
        $em->persist($adminActivity);
        $em->flush();
    }
}
