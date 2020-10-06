<?php

namespace App\Repository;

use App\Entity\ProfilsUtilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfilsUtilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfilsUtilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfilsUtilisateur[]    findAll()
 * @method ProfilsUtilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfilsUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfilsUtilisateur::class);
    }

    // /**
    //  * @return ProfilsUtilisateur[] Returns an array of ProfilsUtilisateur objects
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
    public function findOneBySomeField($value): ?ProfilsUtilisateur
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
