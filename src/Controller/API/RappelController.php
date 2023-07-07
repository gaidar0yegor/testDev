<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Listener\UseCache;
use App\Repository\RappelRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/api/rappel")
 */
class RappelController extends AbstractController
{
    /**
     * @UseCache()
     * @Cache(maxage="300")
     *
     * @Route(
     *      "/notifications/{id}",
     *      methods={"GET"},
     *      name="api_rappel_user_notifications"
     * )
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function getLastNotifications(
        User $user,
        RappelRepository $rappelRepository,
        NormalizerInterface $normalizer
    ): JsonResponse {
        $rappels = $rappelRepository->findLastFor($user);

        return new JsonResponse([
            'rappels' => $normalizer->normalize($rappels),

            // TMP
            'cache' => [
                'user' => $user->getFullname()
            ],
        ]);
    }

    /**
     * @Route(
     *      "/notifications/{id}",
     *      methods={"POST"},
     *      name="api_rappel_user_notifications_acknowledge"
     * )
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function acknowledge(
        User $user,
        Request $request,
        RappelRepository $rappelRepository
    ): JsonResponse {
        $content = $request->toArray();

        if (!isset($content['acknowledgeIds']) || !is_array($content['acknowledgeIds'])) {
            throw new BadRequestException('Excepted parameters like {"acknowledgeIds": int[]}');
        }

        $rappelRepository->acknowledgeAllFor($user);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
