<?php

namespace App\Repository;

use App\Entity\FaitMarquantComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FaitMarquantComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaitMarquantComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaitMarquantComment[]    findAll()
 * @method FaitMarquantComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaitMarquantCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaitMarquantComment::class);
    }

    // /**
    //  * @return FaitMarquantComment[] Returns an array of FaitMarquantComment objects
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
    public function findOneBySomeField($value): ?FaitMarquantComment
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
