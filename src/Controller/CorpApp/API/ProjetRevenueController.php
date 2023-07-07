<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Projet;
use App\Entity\ProjetBudgetExpense;
use App\Entity\ProjetRevenue;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/api/projet/{projetId}/revenue")
 */
class ProjetRevenueController extends AbstractController
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
     *      name="api_save_projet_revenue"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function saveRevenue(Projet $projet, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $projet);

        $revenue = $request->request->get('updateId')
            ? $this->em->getRepository(ProjetRevenue::class)->find($request->request->get('updateId'))
            : new ProjetRevenue();

        $revenue->setTitre($request->request->get('titre'));
        $revenue->setAmount($request->request->get('amount'));

        $date = $request->get('date');

        if ($date){
            if (\DateTime::createFromFormat('d/m/Y', $date)) {
                $revenue->setDate(\DateTime::createFromFormat('d/m/Y', $date));
            } else {
                throw new BadRequestHttpException('Date entree is invalid');
            }
        }

        $revenue->setProjet($projet);

        $this->em->persist($revenue);
        $this->em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "id" => $revenue->getId(),
            "titre" => $revenue->getTitre(),
            "amount" => number_format((float)$revenue->getAmount(), 2 , ".", ""),
            "date" => $revenue->getDate() ? $revenue->getDate()->format('d/m/Y') : '',
        ]);
    }

    /**
     * @Route(
     *      "/delete/{revenueId}",
     *      methods={"DELETE"},
     *      name="api_destroy_projet_revenue"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetRevenue", options={"id" = "revenueId"})
     */
    public function destroyRevenue(Projet $projet, ProjetRevenue $projetRevenue, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $projet);

        $amount = $projetRevenue->getAmount();
        $this->em->remove($projetRevenue);
        $this->em->flush();

        return new JsonResponse([
            "action" => "deleted",
            "amount" => $amount,
        ]);
    }
}
