<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserQuitteSociete implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    public function __construct(EntityLinkService $entityLinkService)
    {
        $this->entityLinkService = $entityLinkService;
    }

    public static function getType(): string
    {
        return 'user_quitte_societe';
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
        return sprintf(
            '%s %s a quitté la société.',
            '<i class="fa fa-user-times" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['user'])
        );
    }

    public function postUpdate(User $user, LifecycleEventArgs $args): ?Activity
    {
        $em = $args->getEntityManager();

        $oldUserActivities = $em
            ->createQueryBuilder()
            ->from(UserActivity::class, 'userActivity')
            ->select('userActivity')
            ->leftJoin('userActivity.activity', 'activity')
            ->where('userActivity.user = :user')
            ->andWhere('activity.type = :type')
            ->setParameters([
                'user' => $user,
                'type' => self::getType(),
            ])
            ->getQuery()
            ->getResult()
        ;

        foreach ($oldUserActivities as $oldUserActivity) {
            $em->remove($oldUserActivity->getActivity());
            $em->remove($oldUserActivity);
        }

        if (null === $user->getDateSortie()) {
            $em->flush();
            return null;
        }

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setDatetime($user->getDateSortie())
            ->setParameters([
                'user' => intval($user->getId()),
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

        return $activity;
    }
}
