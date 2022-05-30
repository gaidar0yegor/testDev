<?php

namespace App\Repository;

use App\Entity\EvenementParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvenementParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvenementParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvenementParticipant[]    findAll()
 * @method EvenementParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvenementParticipant::class);
    }

    // /**
    //  * @return EvenementParticipant[] Returns an array of EvenementParticipant objects
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
    public function findOneBySomeField($value): ?EvenementParticipant
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
