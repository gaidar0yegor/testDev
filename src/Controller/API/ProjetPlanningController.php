<?php

namespace App\Controller\API;

use App\Entity\Projet;
use App\Entity\ProjetPlanning;
use App\Entity\ProjetPlanningTask;
use App\MultiSociete\UserContext;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/api/projet/{projetId}/planning")
 */
class ProjetPlanningController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected UserContext $userContext;

    public function __construct(EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->userContext = $userContext;
    }

    /**
     * @Route(
     *      "/list",
     *      methods={"GET"},
     *      name="api_get_projet_planning_tasks_gantt"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function getTasksForGantt(Projet $projet)
    {
        if (null === $projet->getProjetPlanning()){
            return new JsonResponse([
                "data" => [],
            ]);
        }

        $planningTasks = $this->em->getRepository(ProjetPlanningTask::class)->getForGanttByProjet($projet);

        return new JsonResponse([
            "data" => $planningTasks,
        ]);
    }

    /**
     * @Route(
     *      "/task",
     *      methods={"POST"},
     *      name="api_save_projet_planning_task_gantt"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function saveTaskFromGantt(Projet $projet, Request $request)
    {
        $planning = $projet->getProjetPlanning();

        if (null === $planning){
            $planning = new ProjetPlanning();
            $planning->setProjet($projet);
            $planning->setCreatedAt(new DateTime());
            $planning->setCreatedBy($this->userContext->getSocieteUser());
        } else {
            $planning->setUpdatedAt(new DateTime());
        }

        $projetPlanningTask = new ProjetPlanningTask();
        $projetPlanningTask->setText($request->request->get('text'));
        $projetPlanningTask->setStartDate(\DateTime::createFromFormat('d/m/Y H:i', $request->request->get('start_date') . ' 00:00'));
        $projetPlanningTask->setDuration((int)$request->request->get('duration'));
        $projetPlanningTask->setProgress((float)$request->request->get('progress'));

        $parentTask = (int)$request->request->get('parent') === 0 ? null :
            $this->em->getRepository(ProjetPlanningTask::class)->find($request->request->get('parent'));

        $projetPlanningTask->setParentTask($parentTask);

        $planning->addProjetPlanningTask($projetPlanningTask);

        $this->em->persist($planning);
        $this->em->persist($projetPlanningTask);
        $this->em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "tid" => $projetPlanningTask->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/task/{taskId}",
     *      methods={"PUT"},
     *      name="api_update_projet_planning_task_gantt"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetPlanningTask", options={"id" = "taskId"})
     */
    public function updateTaskFromGantt(Projet $projet, ProjetPlanningTask $projetPlanningTask, Request $request)
    {
        $projetPlanningTask->setText($request->request->get('text'));
        $projetPlanningTask->setStartDate(\DateTime::createFromFormat('d/m/Y H:i', $request->request->get('start_date') . ' 00:00'));
        $projetPlanningTask->setDuration((int)$request->request->get('duration'));
        $projetPlanningTask->setProgress((float)$request->request->get('progress'));

        $parentTask = (int)$request->request->get('parent') === 0 ? null :
            $this->em->getRepository(ProjetPlanningTask::class)->find($request->request->get('parent'));

        $projetPlanningTask->setParentTask($parentTask);

        $planning = $projetPlanningTask->getProjetPlanning();
        $planning->setUpdatedAt(new DateTime());

        $this->em->persist($planning);
        $this->em->persist($projetPlanningTask);
        $this->em->flush();

        return new JsonResponse([
            "action" => "updated"
        ]);
    }

    /**
     * @Route(
     *      "/task/{taskId}",
     *      methods={"DELETE"},
     *      name="api_destroy_projet_planning_task_gantt"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetPlanningTask", options={"id" = "taskId"})
     */
    public function destroyTaskFromGantt(Projet $projet, ProjetPlanningTask $projetPlanningTask, Request $request)
    {
        $planning = $projetPlanningTask->getProjetPlanning();
        $planning->setUpdatedAt(new DateTime());

        $this->em->remove($projetPlanningTask);
        $this->em->persist($planning);
        $this->em->flush();

        return new JsonResponse([
            "action" => "deleted"
        ]);
    }

}
