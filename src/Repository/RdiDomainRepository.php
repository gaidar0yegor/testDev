<?php

namespace App\Repository;

use App\Entity\RdiDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RdiDomain|null find($id, $lockMode = null, $lockVersion = null)
 * @method RdiDomain|null findOneBy(array $criteria, array $orderBy = null)
 * @method RdiDomain[]    findAll()
 * @method RdiDomain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RdiDomainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RdiDomain::class);
    }

    // /**
    //  * @return RdiDomain[] Returns an array of RdiDomain objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RdiDomain
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
