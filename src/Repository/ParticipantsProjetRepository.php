<?php

namespace App\Repository;

use App\Entity\ParticipantsProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ParticipantsProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParticipantsProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParticipantsProjet[]    findAll()
 * @method ParticipantsProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantsProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipantsProjet::class);
    }

    // /**
    //  * @return ParticipantsProjet[] Returns an array of ParticipantsProjet objects
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
    public function findOneBySomeField($value): ?ParticipantsProjet
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
