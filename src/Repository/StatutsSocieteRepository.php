<?php

namespace App\Repository;

use App\Entity\StatutsSociete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StatutsSociete|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatutsSociete|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatutsSociete[]    findAll()
 * @method StatutsSociete[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutSocieteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatutsSociete::class);
    }

    // /**
    //  * @return StatutsSociete[] Returns an array of StatutsSociete objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StatutsSociete
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
