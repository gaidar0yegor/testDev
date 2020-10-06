<?php

namespace App\Repository;

use App\Entity\FichiersProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FichiersProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method FichiersProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method FichiersProjet[]    findAll()
 * @method FichiersProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichiersProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FichiersProjet::class);
    }

    // /**
    //  * @return FichiersProjet[] Returns an array of FichiersProjet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FichiersProjet
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
