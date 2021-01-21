<?php

namespace App\Activity\Type;

use App\Activity\ActivityEvent;
use App\Activity\ActivityHandlerInterface;
use App\Entity\Activity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRejointSociete implements ActivityHandlerInterface
{
    public const TYPE = 'user_rejoint_societe';

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
            'user',
        ]);

        $resolver->setAllowedTypes('user', 'integer');
    }

    public function render(array $activityParameters): string
    {
        return sprintf(
            '%s %s a rejoint la société.',
            '<i class="fa fa-user-plus" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['user'])
        );
    }

    public function getSubscribedEvent(): array
    {
        return [User::class, ActivityEvent::UPDATED];
    }

    /**
     * @param User $user
     */
    public function onEvent($user, EntityManagerInterface $em): ?Activity
    {
        $oldUserActivities = $em
            ->createQueryBuilder()
            ->from(UserActivity::class, 'userActivity')
            ->select('userActivity')
            ->leftJoin('userActivity.activity', 'activity')
            ->where('userActivity.user = :user')
            ->andWhere('activity.type = :type')
            ->setParameters([
                'user' => $user,
                'type' => self::TYPE,
            ])
            ->getQuery()
            ->getResult()
        ;

        foreach ($oldUserActivities as $oldUserActivity) {
            $em->remove($oldUserActivity->getActivity());
            $em->remove($oldUserActivity);
        }

        if (null === $user->getDateEntree()) {
            $em->flush();
            return null;
        }

        $activity = new Activity();
        $activity
            ->setType(self::TYPE)
            ->setDatetime($user->getDateEntree())
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
