<?php

namespace App\Controller\CorpApp\API;

use App\Service\CraService;
use App\MultiSociete\UserContext;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class CraController extends AbstractController
{
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
        DateTime $month,
        CraService $craService,
        UserContext $userContext,
        EntityManagerInterface $em
    ) {
        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);

        $cra
            ->setJours($request->get('cra'))
            ->setCraModifiedAt(new \DateTime())
        ;

        $em->persist($cra);
        $em->flush($cra);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
