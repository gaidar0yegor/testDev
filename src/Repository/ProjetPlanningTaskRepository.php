<?php

namespace App\Repository;

use App\Entity\ProjetPlanningTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function getForLateNotification() : array
    {
        return $this->createQueryBuilder('ppt')
            ->innerJoin('ppt.projetPlanning', 'projetPlanning')
            ->innerJoin('projetPlanning.projet', 'projet')
            ->andWhere('ppt.progress < 1')
            ->andWhere("ppt.endDate = DATE_ADD(CURRENT_DATE(), projet.nbrDaysNotifTaskEcheance, 'day')")
            ->getQuery()
            ->getResult();
    }
}
