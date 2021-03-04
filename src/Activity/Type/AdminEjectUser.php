<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class AdminEjectUser implements ActivityInterface
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
        return 'admin_eject_user';
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

    public function render(array $activityParameters, string $activityType): string
    {
        if($activityParameters['userEnabled'] === true) {
            return sprintf(
                "%s %s a ré-activé le compte de %s",
                '<i class="fa fa-check-circle-o" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(User::class, $activityParameters['modifiedBy']),
                $this->entityLinkService->generateLink(User::class, $activityParameters['user'])
        );}
        return sprintf("%s %s a desactivé le compte de %s",
            '<i class="fa fa-ban" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['modifiedBy']),
            $this->entityLinkService->generateLink(User::class, $activityParameters['user'])
         );
    }

    public function postUpdate(User $user, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $modifiedBy = $this->security->getUser();
        $userEnabled = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($user);

        if(isset($userEnabled['enabled']))
        {
            if (!$modifiedBy instanceof User) {
                throw new RuntimeException('Impossible to get current user to determine who modified the status');
            }

            $activity = new Activity();
            $activity
                ->setType(self::getType())
                ->setParameters([
                    'user' => intval($user->getId()),
                    'modifiedBy' => intval($modifiedBy->getId()),
                    'userEnabled' => $userEnabled['enabled'][1],
                ]);

            $userActivity = new UserActivity();
            $userActivity
                ->setUser($user)
                ->setActivity($activity)
            ;

            $adminActivity = new UserActivity();
            $adminActivity
                ->setUser($modifiedBy)
                ->setActivity($activity)
            ;

            $em->persist($activity);
            $em->persist($userActivity);
            $em->persist($adminActivity);
            $em->flush();
        }
    }
}
