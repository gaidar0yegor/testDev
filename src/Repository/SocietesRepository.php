<?php

namespace App\Repository;

use App\Entity\Societes;
use App\Entity\StatutsSociete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Societes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Societes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Societes[]    findAll()
 * @method Societes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocietesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Societes::class);
    }

    // /**
    //  * @return Societes[] Returns an array of Societes objects
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
    public function findOneBySomeField($value): ?Societes
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

  public function testFindOneBySomeField($value): ?Societes
  {
      return $this->createQueryBuilder('s')
          ->andWhere('s.id = :val')
          ->setParameter('val', $value)
          ->getQuery()
          ->getOneOrNullResult()
      ;
  }


    public function afficheInfosSociete(): ?Societes
    {
            $qb = $this
                ->createQueryBuilder('s')
                ->leftJoin('s.statuts_societe', 'app')
                ->addSelect('statutssociete');

            return $qb
                ->getQuery()
                ->getResult();

    }

    public function listeRaisonSocialeSociete(): ?Societes
    {
        $qb = $this
            ->createQueryBuilder('s')
            ->select('s','statuts')
            ->from(Societes::class, 'statuts')
            ->innerJoin ('s.StatutsSociete','statuts');

        return $qb
            ->getQuery()
            ->getResult();

    }



}
