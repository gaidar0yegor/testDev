<?php

namespace App\Repository;

use App\Entity\Societe;
use App\Entity\StatutsSociete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Societe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Societe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Societe[]    findAll()
 * @method Societe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocieteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Societe::class);
    }

    // /**
    //  * @return Societe[] Returns an array of Societe objects
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
    public function findOneBySomeField($value): ?Societe
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

  public function testFindOneBySomeField($value): ?Societe
  {
      return $this->createQueryBuilder('s')
          ->andWhere('s.id = :val')
          ->setParameter('val', $value)
          ->getQuery()
          ->getOneOrNullResult()
      ;
  }


    public function afficheInfosSociete(): ?Societe
    {
            $qb = $this
                ->createQueryBuilder('s')
                ->leftJoin('s.statuts_societe', 'app')
                ->addSelect('statutssociete');

            return $qb
                ->getQuery()
                ->getResult();

    }

    public function listeRaisonSocialeSociete(): ?Societe
    {
        $qb = $this
            ->createQueryBuilder('s')
            ->select('s','statuts')
            ->from(Societe::class, 'statuts')
            ->innerJoin ('s.StatutsSociete','statuts');

        return $qb
            ->getQuery()
            ->getResult();

    }



}
