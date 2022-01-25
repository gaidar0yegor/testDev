<?php

namespace App\Repository;

use App\Entity\DashboardConsolide;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DashboardConsolide|null find($id, $lockMode = null, $lockVersion = null)
 * @method DashboardConsolide|null findOneBy(array $criteria, array $orderBy = null)
 * @method DashboardConsolide[]    findAll()
 * @method DashboardConsolide[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DashboardConsolideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DashboardConsolide::class);
    }

    // /**
    //  * @return DashboardConsolide[] Returns an array of DashboardConsolide objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DashboardConsolide
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
