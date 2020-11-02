<?php

namespace App\Controller\API;

use App\Exception\MonthOutOfRangeException;
use App\Service\CraService;
use App\Service\DateMonthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class CraController extends AbstractController
{
    /**
     * Retourne le cra mensuel pour l'utilisateur actuel,
     * soit un cra par défaut si l'utilisateur n'a pas pas encore coché,
     * soit le cra déjà rempli pour permettre de le modification.
     *
     * @Route(
     *      "/cra/{year}/{month}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}", "month"="\d{2}"},
     *      name="api_cra_get"
     * )
     */
    public function getCra(
        string $year,
        string $month,
        DateMonthService $dateMonthService,
        CraService $craService
    ) {
        try {
            $mois = $dateMonthService->getMonthFromYearAndMonth($year, $month);
        } catch (MonthOutOfRangeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $cra = $craService->loadCraForUser($this->getUser(), $mois);

        return new JsonResponse($cra->getJours());
    }

    /**
     * Créer ou met à jour un Cra.
     *
     * @Route(
     *      "/cra/{year}/{month}",
     *      methods={"POST"},
     *      requirements={"year"="\d{4}", "month"="\d{2}"},
     *      name="api_cra_post"
     * )
     */
    public function patchCra(
        Request $request,
        string $year,
        string $month,
        DateMonthService $dateMonthService,
        CraService $craService,
        EntityManagerInterface $em
    ) {
        try {
            $mois = $dateMonthService->getMonthFromYearAndMonth($year, $month);
        } catch (MonthOutOfRangeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        dump($request->get('cra'));

        $cra = $craService->loadCraForUser($this->getUser(), $mois);

        $cra->setJours($request->get('cra'));

        $em->persist($cra);
        $em->flush($cra);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
