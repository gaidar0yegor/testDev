<?php

namespace App\Repository;

use App\Entity\ProjetPlanning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetPlanning|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetPlanning|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetPlanning[]    findAll()
 * @method ProjetPlanning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetPlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetPlanning::class);
    }

    /*
    public function findOneBySomeField($value): ?ProjetPlanning
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
