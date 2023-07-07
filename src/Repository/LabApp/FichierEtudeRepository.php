<?php

namespace App\Repository\LabApp;

use App\Entity\LabApp\FichierEtude;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FichierEtude|null find($id, $lockMode = null, $lockVersion = null)
 * @method FichierEtude|null findOneBy(array $criteria, array $orderBy = null)
 * @method FichierEtude[]    findAll()
 * @method FichierEtude[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichierEtudeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FichierEtude::class);
    }

    // /**
    //  * @return FichierEtude[] Returns an array of FichierEtude objects
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
    public function findOneBySomeField($value): ?FichierEtude
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
