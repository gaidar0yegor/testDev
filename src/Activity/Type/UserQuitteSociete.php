<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
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

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a quitté la société.',
            '<i class="fa fa-user-times" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['user'])
        );
    }

    public function postUpdate(SocieteUser $societeUser, LifecycleEventArgs $args): ?Activity
    {
        $em = $args->getEntityManager();

        $oldUserActivities = $em
            ->createQueryBuilder()
            ->from(SocieteUserActivity::class, 'societeUserActivity')
            ->select('societeUserActivity')
            ->leftJoin('societeUserActivity.activity', 'activity')
            ->where('societeUserActivity.societeUser = :societeUser')
            ->andWhere('activity.type = :type')
            ->setParameters([
                'societeUser' => $societeUser,
                'type' => self::getType(),
            ])
            ->getQuery()
            ->getResult()
        ;

        foreach ($oldUserActivities as $oldUserActivity) {
            $em->remove($oldUserActivity->getActivity());
            $em->remove($oldUserActivity);
        }

        if (null === $societeUser->getDateSortie()) {
            $em->flush();
            return null;
        }

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setDatetime($societeUser->getDateSortie())
            ->setParameters([
                'user' => intval($societeUser->getId()),
            ])
        ;

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($societeUser)
            ->setActivity($activity)
        ;

        $em->persist($activity);
        $em->persist($societeUserActivity);
        $em->flush();

        return $activity;
    }
}
