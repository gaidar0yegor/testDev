<?php

namespace App\Repository;

use App\Entity\DossierFichierProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DossierFichierProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method DossierFichierProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method DossierFichierProjet[]    findAll()
 * @method DossierFichierProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DossierFichierProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DossierFichierProjet::class);
    }

    // /**
    //  * @return DossierFichierProjet[] Returns an array of DossierFichierProjet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DossierFichierProjet
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
