<?php

namespace App\Repository;

use App\Entity\FaitsMarquants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FaitsMarquants|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaitsMarquants|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaitsMarquants[]    findAll()
 * @method FaitsMarquants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaitsMarquantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaitsMarquants::class);
    }

    // /**
    //  * @return FaitsMarquants[] Returns an array of FaitsMarquants objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FaitsMarquants
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
