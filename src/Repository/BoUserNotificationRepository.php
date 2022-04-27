<?php

namespace App\Repository;

use App\Entity\BoUserNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoUserNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoUserNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoUserNotification[]    findAll()
 * @method BoUserNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoUserNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoUserNotification::class);
    }

    public function findByUserByActivityType(User $user, string $activityType, int $limit = null)
    {
        $qb = $this->createQueryBuilder('boUserNotification')
            ->addSelect('activity')
            ->innerJoin('boUserNotification.activity', 'activity')
            ->innerJoin('boUserNotification.boUser', 'user')
            ->andWhere('user = :user')
            ->andWhere('activity.type = :type')
            ->setParameter('type', $activityType)
            ->setParameter('user', $user)
            ->orderBy('activity.datetime', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function checkExistanceNotifsByUser(User $boUser)
    {
        $notif = $this->createQueryBuilder('boUserNotification')
            ->innerJoin('boUserNotification.boUser', 'boUser')
            ->andWhere('boUser = :boUser')
            ->andWhere('boUserNotification.acknowledged = :acknowledged')
            ->setParameter('boUser', $boUser)
            ->setParameter('acknowledged', 0)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $notif instanceof BoUserNotification;
    }

    /**
     * @param User $user
     */
    public function acknowledgeAllFor(User $boUser): void
    {
        $this->createQueryBuilder('notification')
            ->update(BoUserNotification::class, 'notification')
            ->set('notification.acknowledged', true)
            ->where('notification.boUser = :boUser')
            ->setParameter('boUser', $boUser)
            ->getQuery()
            ->execute()
        ;
    }

}
