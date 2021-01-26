<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserActivity;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserActivity[]    findAll()
 * @method UserActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActivity::class);
    }

    public function findByUser(User $user)
    {
        return $this
            ->createQueryBuilder('userActivity')
            ->leftJoin('userActivity.activity', 'activity')
            ->where('userActivity.user = :user')
            ->andWhere('activity.datetime <= :now')
            ->setParameters([
                'user' => $user,
                'now' => new DateTime(),
            ])
            ->orderBy('activity.datetime', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }
}
