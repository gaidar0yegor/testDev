<?php

namespace App\Repository;

use App\Entity\ProjetUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetUrl[]    findAll()
 * @method ProjetUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetUrl::class);
    }

    // /**
    //  * @return ProjetUrl[] Returns an array of ProjetUrl objects
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
    public function findOneBySomeField($value): ?ProjetUrl
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
