<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetPlanningTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetPlanningTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetPlanningTask[]    findAll()
 * @method ProjetPlanningTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetPlanningTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetPlanningTask::class);
    }

    /**
     * @return ProjetPlanningTask[] Returns an array of ProjetPlanningTask objects
     */
    public function getForLateNotification(Projet $projet = null) : array
    {
        $todayPlus3Days = (new \DateTime('today midnight'))->modify('+3 days');

        return $this->createQueryBuilder('ppt')
            ->andWhere('ppt.progress < 1')
            ->andWhere('ppt.endDate = :todayPlus3Days')
            ->setParameter('todayPlus3Days', $todayPlus3Days)
            ->getQuery()
            ->getResult();
    }


}
