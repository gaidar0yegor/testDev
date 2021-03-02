<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\User;
use App\Entity\UserActivity;
// use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class AdminEjectUser implements ActivityInterface
{
    // private EntityLinkService $entityLinkService;

    // public function __construct(EntityLinkService $entityLinkService)
    // {
    //     $this->entityLinkService = $entityLinkService;
    // }
    private Security $security;
    public function __construct(Security $security)
    {
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
        return "L'admin a desactivé votre compte";
        // return sprintf(
        //     '%s %s a desactivé votre compte.',
        //     '<i class="fa fa-user-plus" aria-hidden="true"></i>',
        //     $this->entityLinkService->generateLink(User::class, $activityParameters['user'])
        // );
    }

    public function postUpdate(User $user, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $modifiedBy = $this->security->getUser();

        //if($args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($user)
        //dd($modifiedBy);
        // $oldUserActivities = $em
        //     ->createQueryBuilder()
        //     ->from(UserActivity::class, 'userActivity')
        //     ->select('userActivity')
        //     ->leftJoin('userActivity.activity', 'activity')
        //     ->where('userActivity.user = :user')
        //     ->andWhere('activity.type = :type')
        //     ->setParameters([
        //         'user' => $user,
        //         'type' => self::getType(),
        //     ])
        //     ->getQuery()
        //     ->getResult()
        // ;

        // foreach ($oldUserActivities as $oldUserActivity) {
        //     $em->remove($oldUserActivity->getActivity());
        //     $em->remove($oldUserActivity);
        // }

        // if (null === $user->getDateEntree()) {
        //     $em->flush();
        //     return null;
        // }
        //dd($user);
        if (!$modifiedBy instanceof User) {
            throw new RuntimeException('Impossible to get current user to determine who modified FaitMarquant');
        }
        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'user' => intval($user->getId()),
                'modifiedBy' => intval($modifiedBy->getId()),
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
