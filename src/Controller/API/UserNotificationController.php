<?php

namespace App\Controller\API;

use App\Entity\SocieteUser;
use App\Listener\UseCache;
use App\Repository\SocieteUserNotificationRepository;
use App\Security\Voter\SameUserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/api/user-notifications")
 */
class UserNotificationController extends AbstractController
{
    /**
     * Récupère les dernières notifications utilisateur.
     * L'id correspond au SocieteUser pour lequel récupérer les notifications
     * (doit correspondre à un SocieteUser lié à l'user acutellement connecté)
     *
     * @UseCache()
     * @Cache(maxage="300")
     *
     * @Route(
     *      "/{id}",
     *      methods={"GET"},
     *      name="api_user_notifications"
     * )
     */
    public function getLastNotifications(
        SocieteUser $societeUser,
        SocieteUserNotificationRepository $societeUserNotificationRepository,
        NormalizerInterface $normalizer
    ): JsonResponse {
        $this->denyAccessUnlessGranted(SameUserVoter::NAME, $societeUser);

        $notifications = $societeUserNotificationRepository->findLastFor($societeUser);

        return new JsonResponse([
            'notifications' => $normalizer->normalize($notifications),

            // TMP
            'cache' => [
                'user' => $societeUser->getUser()->getFullname(),
                'societe' => $societeUser->getSociete()->getRaisonSociale(),
            ],
        ]);
    }

    /**
     * Marque toutes les notifications d'un user lues.
     * L'id correspond au SocieteUser pour lequel récupérer les notifications
     * (doit correspondre à un SocieteUser lié à l'user acutellement connecté)
     *
     * @Route(
     *      "/{id}",
     *      methods={"POST"},
     *      name="api_user_notifications_acknowledge"
     * )
     */
    public function acknowledge(
        SocieteUser $societeUser,
        Request $request,
        SocieteUserNotificationRepository $societeUserNotificationRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted(SameUserVoter::NAME, $societeUser);

        $content = $request->toArray();

        if (!isset($content['acknowledgeIds']) || !is_array($content['acknowledgeIds'])) {
            throw new BadRequestException('Excepted parameters like {"acknowledgeIds": int[]}');
        }

        $societeUserNotificationRepository->acknowledgeAllFor(
            $societeUser,
            $content['acknowledgeIds']
        );

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
