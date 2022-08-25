<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Projet;
use App\Entity\ProjetBudgetExpense;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        $this->denyAccessUnlessGranted('edit', $projet);

        $budgetExpense = $request->request->get('updateId')
            ? $this->em->getRepository(ProjetBudgetExpense::class)->find($request->request->get('updateId'))
            : new ProjetBudgetExpense();

        $budgetExpense->setTitre($request->request->get('titre'));
        $budgetExpense->setAmount($request->request->get('amount'));

        $date = $request->get('date');

        if ($date){
            if (\DateTime::createFromFormat('d/m/Y', $date)) {
                $budgetExpense->setDate(\DateTime::createFromFormat('d/m/Y', $date));
            } else {
                throw new BadRequestHttpException('Date entree is invalid');
            }
        }

        $budgetExpense->setProjet($projet);

        $this->em->persist($budgetExpense);
        $this->em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "id" => $budgetExpense->getId(),
            "titre" => $budgetExpense->getTitre(),
            "amount" => number_format((float)$budgetExpense->getAmount(), 2 , ".", ""),
            "date" => $budgetExpense->getDate() ? $budgetExpense->getDate()->format('d/m/Y') : '',
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
        $this->denyAccessUnlessGranted('edit', $projet);

        $amount = $projetBudgetExpense->getAmount();
        $this->em->remove($projetBudgetExpense);
        $this->em->flush();

        return new JsonResponse([
            "action" => "deleted",
            "amount" => $amount,
        ]);
    }
}
