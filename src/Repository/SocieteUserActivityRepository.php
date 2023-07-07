<?php

namespace App\Repository;

use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SocieteUserActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocieteUserActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocieteUserActivity[]    findAll()
 * @method SocieteUserActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocieteUserActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocieteUserActivity::class);
    }

    public function findBySocieteUser(SocieteUser $societeUser)
    {
        return $this
            ->createQueryBuilder('societeUserActivity')
            ->leftJoin('societeUserActivity.activity', 'activity')
            ->where('societeUserActivity.societeUser = :societeUser')
            ->andWhere('activity.datetime <= :now')
            ->setParameters([
                'societeUser' => $societeUser,
                'now' => new DateTime(),
            ])
            ->orderBy('activity.datetime', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }
}
