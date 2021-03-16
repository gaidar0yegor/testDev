<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserNotification[]    findAll()
 * @method UserNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNotification::class);
    }

    /**
     * @return UserNotification[]
     */
    public function findLastFor(User $user)
    {
        return $this->createQueryBuilder('notification')
            ->leftJoin('notification.activity', 'activity')
            ->andWhere('notification.user = :user')
            ->setParameter('user', $user)
            ->orderBy('activity.datetime', 'desc')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User $user
     * @param int[] $ids Array of ids of notifications to acknowledge.
     */
    public function acknowledgeAllFor(User $user, array $ids): void
    {
        $this->createQueryBuilder('notification')
            ->update(UserNotification::class, 'notification')
            ->set('notification.acknowledged', true)
            ->where('notification.user = :user')
            ->andWhere('notification.id in (:ids)')
            ->setParameter('user', $user)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute()
        ;
    }
}
