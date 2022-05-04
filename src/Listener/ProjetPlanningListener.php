<?php

namespace App\Listener;

use App\Entity\ProjetPlanning;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ProjetPlanningListener
{
    public function postUpdate(ProjetPlanning $projetPlanning, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $this->updateStats($projetPlanning);

        $em->persist($projetPlanning);
        $em->flush();
    }

    private function updateStats(ProjetPlanning $projetPlanning)
    {
        if ($projetPlanning->getProjetPlanningTasks()->count() > 0){
            $nbrTaskEnded = 0;
            $efficacite = 0;

            foreach ($projetPlanning->getProjetPlanningTasks() as $task){
                if ($task->getProgress() == 1){
                    $nbrTaskEnded++;
                    $endDateReal = $task->getEndDateReal() ? $task->getEndDateReal() : (new \DateTime());
                    $diffDays =  round(($task->getEndDate()->getTimestamp() - $endDateReal->getTimestamp()) / (60 * 60 * 24));

                    if ($diffDays === 0){
                        continue;
                    } elseif ($diffDays > 0) {
                        $efficacite += $diffDays <= $task->getDuration() * (3/4) ? 1 : 0.5;
                    } else {
                        $efficacite -= abs($diffDays) <= $task->getDuration() * (1/4) ? 0.5 : 1;
                    }
                }
            }

            $projetPlanning->setEfficacite($nbrTaskEnded === 0 ? 0 : $efficacite / $nbrTaskEnded);
            $projetPlanning->setEffectivite($nbrTaskEnded / $projetPlanning->getProjetPlanningTasks()->count());
        }
    }
}