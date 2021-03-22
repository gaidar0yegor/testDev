<?php

namespace App\Controller\API;

use App\Listener\UseCache;
use App\Repository\SocieteUserNotificationRepository;
use App\MultiSociete\UserContext;
use Http\Client\Exception\HttpException;
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
     *
     * @UseCache()
     * @Cache(maxage="300", vary={"Cookie"})
     *
     * @Route(
     *      "/",
     *      methods={"GET"},
     *      name="api_user_notifications"
     * )
     */
    public function getLastNotifications(
        SocieteUserNotificationRepository $societeUserNotificationRepository,
        UserContext $userContext,
        NormalizerInterface $normalizer
    ): JsonResponse{
        $notifications = $societeUserNotificationRepository->findLastFor($userContext->getSocieteUser());

        return new JsonResponse([
            'notifications' => $normalizer->normalize($notifications),
        ]);
    }

    /**
     * Marque toutes les notifications d'un user lues.
     *
     * @Route(
     *      "/",
     *      methods={"POST"},
     *      name="api_user_notifications_acknowledge"
     * )
     */
    public function acknowledge(
        Request $request,
        UserContext $userContext,
        SocieteUserNotificationRepository $societeUserNotificationRepository
    ): JsonResponse {
        $content = $request->toArray();

        if (!isset($content['acknowledgeIds']) || !is_array($content['acknowledgeIds'])) {
            throw new BadRequestException('Excepted parameters like {"acknowledgeIds": int[]}');
        }

        $societeUserNotificationRepository->acknowledgeAllFor(
            $userContext->getSocieteUser(),
            $content['acknowledgeIds']
        );

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
