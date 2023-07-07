<?php

namespace App\Repository;

use App\Entity\SocieteUser;
use App\Entity\SocieteUserEvenementNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SocieteUserEvenementNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocieteUserEvenementNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocieteUserEvenementNotification[]    findAll()
 * @method SocieteUserEvenementNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocieteUserEvenementNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocieteUserEvenementNotification::class);
    }

    /**
     * @return SocieteUserEvenementNotification[]
     */
    public function findLastFor(SocieteUser $societeUser)
    {
        return $this->createQueryBuilder('notification')
            ->leftJoin('notification.activity', 'activity')
            ->andWhere('notification.societeUser = :societeUser')
            ->setParameter('societeUser', $societeUser)
            ->orderBy('activity.datetime', 'desc')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param SocieteUser $societeUser
     * @param int[] $ids Array of ids of notifications to acknowledge.
     */
    public function acknowledgeAllFor(SocieteUser $societeUser, array $ids): void
    {
        $this->createQueryBuilder('notification')
            ->update(SocieteUserEvenementNotification::class, 'notification')
            ->set('notification.acknowledged', true)
            ->where('notification.societeUser = :societeUser')
            ->andWhere('notification.id in (:ids)')
            ->setParameter('societeUser', $societeUser)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute()
        ;
    }
}
