<?php

namespace App\Repository;

use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SocieteUserPeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocieteUserPeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocieteUserPeriod[]    findAll()
 * @method SocieteUserPeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocieteUserPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocieteUserPeriod::class);
    }

    public function findByDateEntryNotNullAndDateLeaveNull(SocieteUser $societeUser)
    {
        return $this->createQueryBuilder('sup')
            ->andWhere('sup.societeUser = :societeUser')
            ->andWhere('sup.dateEntry is not null')
            ->andWhere('sup.dateLeave is null')
            ->setParameter('societeUser', $societeUser)
            ->getQuery()
            ->getResult()
        ;
    }
}
