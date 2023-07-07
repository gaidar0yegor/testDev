<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\ProjetSuspendPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetSuspendPeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetSuspendPeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetSuspendPeriod[]    findAll()
 * @method ProjetSuspendPeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetSuspendPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetSuspendPeriod::class);
    }

    public function findToResume(Projet $projet): ?ProjetSuspendPeriod
    {
        return $this->createQueryBuilder('psp')
            ->andWhere('psp.projet = :projet')
            ->andWhere('psp.suspendedAt IS NOT NULL')
            ->andWhere('psp.resumedAt IS NULL')
            ->setParameter('projet', $projet)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
