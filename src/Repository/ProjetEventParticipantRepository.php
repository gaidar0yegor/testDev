<?php

namespace App\Repository;

use App\Entity\ProjetEventParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetEventParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetEventParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetEventParticipant[]    findAll()
 * @method ProjetEventParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetEventParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetEventParticipant::class);
    }

    // /**
    //  * @return ProjetEventParticipant[] Returns an array of ProjetEventParticipant objects
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
    public function findOneBySomeField($value): ?ProjetEventParticipant
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
