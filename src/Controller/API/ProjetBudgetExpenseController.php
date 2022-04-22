<?php

namespace App\Controller\API;

use App\Entity\Projet;
use App\Entity\ProjetBudgetExpense;
use App\Entity\ProjetParticipant;
use App\Entity\ProjetPlanning;
use App\Entity\ProjetPlanningTask;
use App\MultiSociete\UserContext;
use App\Notification\Event\ProjetParticipantTaskAssignedEvent;
use App\Security\Role\RoleProjet;
use App\Service\ParticipantService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/api/projet/{projetId}/budget-expense")
 */
class ProjetBudgetExpenseController extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route(
     *      "/save",
     *      methods={"POST"},
     *      name="api_save_projet_budget_expense"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function saveBudgetExpense(Projet $projet, Request $request)
    {
        $budgetExpense = new ProjetBudgetExpense();
        $budgetExpense->setTitre($request->request->get('titre'));
        $budgetExpense->setAmount($request->request->get('amount'));
        $budgetExpense->setProjet($projet);

        $this->em->persist($budgetExpense);
        $this->em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "id" => $budgetExpense->getId(),
            "titre" => $budgetExpense->getTitre(),
            "amount" => number_format((float)$budgetExpense->getAmount(), 2)
        ]);
    }

    /**
     * @Route(
     *      "/delete/{expenseId}",
     *      methods={"DELETE"},
     *      name="api_destroy_projet_budget_expense"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetBudgetExpense", options={"id" = "expenseId"})
     */
    public function destroyBudgetExpense(Projet $projet, ProjetBudgetExpense $projetBudgetExpense, Request $request)
    {
        $amount = $projetBudgetExpense->getAmount();
        $this->em->remove($projetBudgetExpense);
        $this->em->flush();

        return new JsonResponse([
            "action" => "deleted",
            "amount" => $amount,
        ]);
    }
}
