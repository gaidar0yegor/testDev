<?php

namespace App\Repository;

use App\Entity\ProjetBudgetExpense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetBudgetExpense|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetBudgetExpense|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetBudgetExpense[]    findAll()
 * @method ProjetBudgetExpense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetBudgetExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetBudgetExpense::class);
    }

    // /**
    //  * @return ProjetBudgetExpense[] Returns an array of ProjetBudgetExpense objects
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
    public function findOneBySomeField($value): ?ProjetBudgetExpense
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
