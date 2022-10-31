<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\ProjetPlanning;
use App\Entity\ProjetPlanningTask;
use App\MultiSociete\UserContext;
use App\Notification\Event\ProjetParticipantTaskAssignedEvent;
use App\Security\Role\RoleProjet;
use App\Service\ParticipantService;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/api/projet/{projetId}/planning")
 */
class ProjetPlanningController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected UserContext $userContext;
    private EventDispatcherInterface $dispatcher;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        UserContext $userContext,
        EventDispatcherInterface $dispatcher,
        TranslatorInterface $translator
    )
    {
        $this->em = $em;
        $this->userContext = $userContext;
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
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

        $tasks = [];
        foreach ($projet->getProjetPlanning()->getProjetPlanningTasks() as $planningTask){
            $isParticipant = false;
            if ($this->userContext->hasSocieteUser()){
                foreach ($planningTask->getParticipants() as $participant){
                    if ($participant->getSocieteUser() === $this->userContext->getSocieteUser()){
                        $isParticipant = true;break;
                    }
                }
            }
            $tasks[] = [
                'id' => $planningTask->getId(),
                'text' => $planningTask->getText(),
                'description' => $planningTask->getDescription(),
                'duration' => $planningTask->getDuration(),
                'progress' => $planningTask->getProgress(),
                'start_date' => $planningTask->getStartDate()->format('d/m/Y'),
                'parent' => is_object($planningTask->getParentTask()) ? $planningTask->getParentTask()->getId() : 0,
                'is_participant' => $isParticipant,
                'open' => true,
            ];
        }

        return new JsonResponse([
            "data" => $tasks,
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
        $projetPlanningTask->setDescription($request->request->get('description'));

        $startdDate = \DateTime::createFromFormat('d/m/Y H:i', $request->request->get('start_date') . ' 00:00');
        $projetPlanningTask->setStartDate($startdDate);
        $projetPlanningTask->setDuration((int)$request->request->get('duration'));
        $projetPlanningTask->setProgress((float)$request->request->get('progress'));

        $projetPlanningTask->setEndDate((clone $startdDate)->modify("+" . $projetPlanningTask->getDuration() - 1 . " days"));

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
        $projetPlanningTask->setDescription($request->request->get('description'));
        $newProgress = (float)$request->request->get('progress');
        $linkFaitMarquant = false;
        if ($newProgress == 1 && $newProgress != $projetPlanningTask->getProgress()){
            if ($projetPlanningTask->getParentTask() === null){
                $lotId = $projetPlanningTask->getId();
            } elseif ($projetPlanningTask->getParentTask()->getParentTask() === null){
                $lotId = $projetPlanningTask->getParentTask()->getId();
            } else {
                $lotId = $projetPlanningTask->getParentTask()->getParentTask()->getId();
            }
            $linkFaitMarquant = $this->generateUrl('corp_app_fo_fait_marquant_ajouter', ['projetId' => $projet->getId(), 'link_task' => $lotId]);
        }

        $startdDate = \DateTime::createFromFormat('d/m/Y H:i', $request->request->get('start_date') . ' 00:00');
        $projetPlanningTask->setStartDate($startdDate);
        $projetPlanningTask->setDuration((int)$request->request->get('duration'));
        $projetPlanningTask->setProgress($newProgress);

        $projetPlanningTask->setEndDate((clone $startdDate)->modify("+" . $projetPlanningTask->getDuration() - 1 . " days"));

        $parentTask = (int)$request->request->get('parent') === 0 ? null :
            $this->em->getRepository(ProjetPlanningTask::class)->find($request->request->get('parent'));

        $projetPlanningTask->setParentTask($parentTask);

        $planning = $projetPlanningTask->getProjetPlanning();
        $planning->setUpdatedAt(new DateTime());

        $this->em->persist($planning);
        $this->em->persist($projetPlanningTask);
        $this->em->flush();

        return new JsonResponse([
            "action" => "updated",
            "linkFaitMarquant" => $linkFaitMarquant,
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

    /**
     * @Route(
     *      "/participants/{taskId}",
     *      methods={"GET"},
     *      name="api_get_participants_for_projet_planning_task_gantt"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetPlanningTask", options={"id" = "taskId"})
     */
    public function getParticipantsForTak(Projet $projet, ProjetPlanningTask $projetPlanningTask, ParticipantService $participantService)
    {
        if (!ProductPrivilegeCheker::checkProductPrivilege($projet->getSociete(),ProductPrivileges::PLANIFICATION_PROJET_AVANCE)){
            return new JsonResponse([
                'message' => $this->translator->trans('product_privilege_no_dispo')
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $contributeurs = $participantService->getProjetParticipantsWithRole(
            $projet->getActiveProjetParticipants(),
            RoleProjet::CONTRIBUTEUR
        );

        $data = [];
        foreach ($contributeurs as $contributeur){
            $data[] = [
                'id' => $contributeur->getId(),
                'fullName' => $contributeur->getSocieteUser()->getUser()->getFullname(),
                'assigned' => $projetPlanningTask->getParticipants()->contains($contributeur),
            ];
        }

        return new JsonResponse([
            "data" => $data,
        ]);
    }

    /**
     * @Route(
     *      "/participants/{taskId}",
     *      methods={"POST"},
     *      name="api_post_participants_for_projet_planning_task_gantt"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetPlanningTask", options={"id" = "taskId"})
     */
    public function postParticipantsForTak(Projet $projet, ProjetPlanningTask $projetPlanningTask, Request $request)
    {
        $assignedParticipants = new ArrayCollection($this->em->getRepository(ProjetParticipant::class)->findBy(array('id' => $request->request->get('assigned'))));

        $toAddToChilds = [];
        foreach ($assignedParticipants as $assignedParticipant){
            if (!$projetPlanningTask->getParticipants()->contains($assignedParticipant)){
                $projetPlanningTask->addParticipant($assignedParticipant);
                $toAddToChilds[] = $assignedParticipant;
                $this->dispatcher->dispatch(new ProjetParticipantTaskAssignedEvent($projetPlanningTask, $assignedParticipant));
            }
        }

        $toRemoveFromChilds = [];
        foreach ($projetPlanningTask->getParticipants() as $projetParticipant){
            if (!$assignedParticipants->contains($projetParticipant)){
                $projetPlanningTask->removeParticipant($projetParticipant);
                $toRemoveFromChilds[] = $projetParticipant;
            }
        }

        $this->em->persist($projetPlanningTask);

        $subTasks = $projetPlanningTask->getAllLevelsChildren();
        foreach ($subTasks as $subTask){
            foreach ($toAddToChilds as $toAddToChild){
                if (!$subTask->getParticipants()->contains($toAddToChild)){
                    $subTask->addParticipant($toAddToChild);
                    $this->dispatcher->dispatch(new ProjetParticipantTaskAssignedEvent($subTask, $toAddToChild));
                }
            }

            foreach ($toRemoveFromChilds as $toRemoveFromChild){
                if ($subTask->getParticipants()->contains($toRemoveFromChild)){
                    $subTask->removeParticipant($toRemoveFromChild);
                }
            }

            $this->em->persist($subTask);
        }

        $this->em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
