<?php

namespace App\Repository;

use App\Entity\StatutsProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StatutsProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatutsProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatutsProjet[]    findAll()
 * @method StatutsProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutsProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatutsProjet::class);
    }

    // /**
    //  * @return StatutsProjet[] Returns an array of StatutsProjet objects
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
    public function findOneBySomeField($value): ?StatutsProjet
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
