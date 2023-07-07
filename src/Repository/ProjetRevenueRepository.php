<?php

namespace App\Repository;

use App\Entity\ProjetRevenue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetRevenue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetRevenue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetRevenue[]    findAll()
 * @method ProjetRevenue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRevenueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetRevenue::class);
    }

    // /**
    //  * @return ProjetRevenue[] Returns an array of ProjetRevenue objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProjetRevenue
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
