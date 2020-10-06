<?php

namespace App\Repository;

use App\Entity\StatutsRdiDuProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StatutsRdiDuProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatutsRdiDuProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatutsRdiDuProjet[]    findAll()
 * @method StatutsRdiDuProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutsRdiDuProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatutsRdiDuProjet::class);
    }

    // /**
    //  * @return StatutsRdiDuProjet[] Returns an array of StatutsRdiDuProjet objects
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
    public function findOneBySomeField($value): ?StatutsRdiDuProjet
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
