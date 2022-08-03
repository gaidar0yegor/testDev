<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;
use App\MultiSociete\UserContext;
use App\ProjetResourceInterface;
use App\Security\Role\RoleProjet;
use App\Service\ParticipantService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/projet/{projetId}/planning")
 */
class ProjetPlanningController extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="corp_app_fo_projet_planning", requirements={"projetId"="\d+"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function show(Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('corp_app/projets/planning.html.twig', [
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet),
        ]);
    }

    /**
     * @Route("/task/{planningTaskId}", name="corp_app_fo_projet_fm_per_planning_task", requirements={"planningTaskId"="\d+"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetPlanningTask", options={"id" = "planningTaskId"})
     */
    public function faitMarquantPerTask(
        Projet $projet,
        ProjetPlanningTask $projetPlanningTask,
        ParticipantService $participantService,
        UserContext $userContext
    )
    {
        $this->denyAccessUnlessGranted('view', $projet);

        $faitMarquants = $projetPlanningTask->getFaitMarquants();

        return $this->render('corp_app/projets/fiche_projet.html.twig', [
            'projetPlanningTask' => $projetPlanningTask,
            'projet' => $projet,
            'faitMarquants' => $faitMarquants,
            'participation' => $participantService->getProjetParticipant($userContext->getSocieteUser(), $projet),
            'nextEvenements' => $projet->getNextEvenements(),
            'contributeurs' => $participantService->getProjetParticipantsWithRoleExactly(
                $projet->getActiveProjetParticipants(),
                RoleProjet::CONTRIBUTEUR
            ),
            'userCanEditProjet' => $this->isGranted('edit', $projet),
            'userCanAddFaitMarquant' => $this->isGranted(ProjetResourceInterface::CREATE, $projet),
        ]);
    }

}
