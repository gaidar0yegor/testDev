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
        ]);

        $resolver->setAllowedTypes('user', 'integer');
    }

    public function render(array $activityParameters, string $activityType): string
    {
        if($activityParameters['statOfUser'] === false) {
            return sprintf("%s %s a ré-activé le compte de %s",
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
        $stateOfUser = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($user);

        if (!$modifiedBy instanceof User) {
            throw new RuntimeException('Impossible to get current user to determine who modified the status');
        }

            if ($stateOfUser['enabled']['0'] === false){
                $activity = new Activity();
                $activity
                    ->setType(self::getType())
                    ->setParameters([
                        'user' => intval($user->getId()),
                        'modifiedBy' => intval($modifiedBy->getId()),
                        'statOfUser' => false,
                    ]);
            } else {
                $activity = new Activity();
                $activity
                    ->setType(self::getType())
                    ->setParameters([
                        'user' => intval($user->getId()),
                        'modifiedBy' => intval($modifiedBy->getId()),
                        'statOfUser' => true,
                    ]);
            }
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
