<?php

namespace App\Repository;

use App\Entity\JoursAbsence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JoursAbsence|null find($id, $lockMode = null, $lockVersion = null)
 * @method JoursAbsence|null findOneBy(array $criteria, array $orderBy = null)
 * @method JoursAbsence[]    findAll()
 * @method JoursAbsence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JoursAbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JoursAbsence::class);
    }

    // /**
    //  * @return JoursAbsence[] Returns an array of JoursAbsence objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JoursAbsence
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
