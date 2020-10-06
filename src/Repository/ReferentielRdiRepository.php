<?php

namespace App\Repository;

use App\Entity\ReferentielRdi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReferentielRdi|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReferentielRdi|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReferentielRdi[]    findAll()
 * @method ReferentielRdi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferentielRdiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferentielRdi::class);
    }

    // /**
    //  * @return ReferentielRdi[] Returns an array of ReferentielRdi objects
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
    public function findOneBySomeField($value): ?ReferentielRdi
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
