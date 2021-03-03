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
        if($activityParameters['statOfUser'] == false) {
            return sprintf("%s %s a ré-activé votre compte",
            '<i class="fa fa-user-plus" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['modifiedBy'])
        );
        }

        return sprintf("%s %s a desactivé votre compte",
        '<i class="fa fa-close" aria-hidden="true"></i>',
        $this->entityLinkService->generateLink(User::class, $activityParameters['modifiedBy'])
        );
    }

    public function postUpdate(User $user, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $modifiedBy = $this->security->getUser();

        $stateOfUser = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($user);

            if ($stateOfUser['enabled']['0'] == false){
                if (!$modifiedBy instanceof User) {
                    throw new RuntimeException('Impossible to get current user to determine who modified FaitMarquant');
                }
                $activity = new Activity();
                $activity
                    ->setType(self::getType())
                    ->setParameters([
                        'user' => intval($user->getId()),
                        'modifiedBy' => intval($modifiedBy->getId()),
                        'statOfUser' => false,
                    ])
                ;

                $userActivity = new UserActivity();
                $userActivity
                    ->setUser($user)
                    ->setActivity($activity)
                ;

                $em->persist($activity);
                $em->persist($userActivity);
                $em->flush();

            } else {
                if (!$modifiedBy instanceof User) {
                    throw new RuntimeException('Impossible to get current user to determine who modified FaitMarquant');
                }
                $activity = new Activity();
                $activity
                    ->setType(self::getType())
                    ->setParameters([
                        'user' => intval($user->getId()),
                        'modifiedBy' => intval($modifiedBy->getId()),
                        'statOfUser' => true,
                    ])
                ;

                $userActivity = new UserActivity();
                $userActivity
                    ->setUser($user)
                    ->setActivity($activity)
                ;

                $em->persist($activity);
                $em->persist($userActivity);
                $em->flush();

            }
    }
}
