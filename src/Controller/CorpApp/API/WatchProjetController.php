<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Projet;
use App\MultiSociete\UserContext;
use App\Service\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/projet")
 */
class WatchProjetController extends AbstractController
{
    /**
     * Suivre ou ne plus suivre un projet
     * pour recevoir toutes les notifications.
     *
     * @Route(
     *      "/{id}/{watchOrUnwatch}",
     *      methods={"POST"},
     *      requirements={"watchOrUnwatch": "^watch|unwatch$"},
     *      name="api_watch_projet_watch"
     * )
     */
    public function watch(
        Projet $projet,
        string $watchOrUnwatch,
        UserContext $userContext,
        ParticipantService $participantService,
        EntityManagerInterface $em
    ) {
        $projetParticipant = $participantService->getProjetParticipant($userContext->getSocieteUser(), $projet);

        if (null === $projetParticipant) {
            throw new AccessDeniedHttpException("Cannot $watchOrUnwatch a projet you don't have access to.");
        }

        $watch = 'watch' === $watchOrUnwatch;

        if ($projetParticipant->getWatching() === $watch) {
            return new JsonResponse('This projet is already '.$watchOrUnwatch, JsonResponse::HTTP_CONFLICT);
        }

        $projetParticipant->setWatching($watch);

        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
