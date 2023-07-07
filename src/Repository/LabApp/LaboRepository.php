<?php

namespace App\Repository\LabApp;

use App\Entity\LabApp\Labo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Labo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Labo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Labo[]    findAll()
 * @method Labo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LaboRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Labo::class);
    }

    // /**
    //  * @return Labo[] Returns an array of Labo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Labo
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
