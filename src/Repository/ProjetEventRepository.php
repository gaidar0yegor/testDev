<?php

namespace App\Repository;

use App\Entity\ProjetEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetEvent[]    findAll()
 * @method ProjetEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetEvent::class);
    }

    // /**
    //  * @return ProjetEvent[] Returns an array of ProjetEvent objects
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
    public function findOneBySomeField($value): ?ProjetEvent
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
