<?php

namespace App\Repository;

use App\Entity\Projet;
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
    public function getForGanttByProjet(Projet $projet) : array
    {
        $tasks = $this->createQueryBuilder('ppt')
            ->select('ppt.id, ppt.text, ppt.startDate as start_date, ppt.duration, ppt.progress')
            ->addSelect('parentTask.id as parent')
            ->leftJoin('ppt.parentTask','parentTask')
            ->join('ppt.projetPlanning', 'pp', 'WITH', 'ppt.projetPlanning = pp')
            ->andWhere('pp.projet = :projet')
            ->setParameter('projet', $projet)
            ->getQuery()
            ->getResult();

        foreach ($tasks as $key => $task){
            $tasks[$key]['start_date'] = ($tasks[$key]['start_date'])->format('d/m/Y');
            $tasks[$key]['parent'] = $tasks[$key]['parent'] ? $tasks[$key]['parent'] : 0;
            $tasks[$key]['open'] = true;
            $tasks[$key]['color'] = $projet->getColorCode();
        }

        return $tasks;
    }

    /**
     * @return ProjetPlanningTask[] Returns an array of ProjetPlanningTask objects
     */
    public function getForLateNotification(Projet $projet = null) : array
    {
        $todayMinus3Days = (new \DateTime('today midnight'))->modify('-3 days');

        return $this->createQueryBuilder('ppt')
            ->andWhere('ppt.endDate = :todayMinus3Days')
            ->setParameter('todayMinus3Days', $todayMinus3Days)
            ->getQuery()
            ->getResult();
    }


}
