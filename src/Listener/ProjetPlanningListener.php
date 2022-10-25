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
        $nbrDaysNotifTaskEcheance = $projetPlanning->getProjet()->getNbrDaysNotifTaskEcheance();

        $nbrTaskEnded = 0;
        $effGlobal = 0;
        $effectivite = 0;

        if ($projetPlanning->getProjetPlanningTasks()->count() > 0){

            foreach ($projetPlanning->getProjetPlanningTasks() as $task){
                if ($task->getProgress() == 1){
                    $nbrTaskEnded++;

                    $endDateReal = $task->getEndDateReal() ? $task->getEndDateReal() : (new \DateTime())->setTime(0,0,0);

                    $T = round(($task->getEndDate()->getTimestamp() - $task->getStartDate()->getTimestamp()) / (60 * 60 * 24));
                    $t = round(($endDateReal->getTimestamp() - $task->getStartDate()->getTimestamp()) / (60 * 60 * 24));

                    if ($T >= 2 * $nbrDaysNotifTaskEcheance){
                        $eff = ($T - $t) / ( 4 * $nbrDaysNotifTaskEcheance);
                    } else{
                        $eff = ($T - $t) / $T;
                    }

                    if ($eff > 1){
                        $eff = 1;
                    } elseif ($eff < -1) {
                        $eff = -1;
                    }

                    $effGlobal += $eff;
                }
            }

            $effGlobal = $nbrTaskEnded === 0 ? 0 : $effGlobal / $nbrTaskEnded;
            $effectivite = $nbrTaskEnded / $projetPlanning->getProjetPlanningTasks()->count();
        }

        $projetPlanning->setEfficacite($effGlobal);
        $projetPlanning->setEffectivite($effectivite);
    }
}
