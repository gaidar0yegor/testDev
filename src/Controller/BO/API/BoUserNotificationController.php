<?php

namespace App\Controller\BO\API;

use App\Entity\SocieteUser;
use App\Entity\User;
use App\Listener\UseCache;
use App\MultiSociete\UserContext;
use App\Repository\BoUserNotificationRepository;
use App\Repository\SocieteUserNotificationRepository;
use App\Security\Voter\SameUserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/api/user-notifications")
 */
class BoUserNotificationController extends AbstractController
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     *
     * @UseCache()
     * @Cache(maxage="300")
     *
     * @Route("", methods={"GET"}, name="bo_api_check_existance_user_notifications")
     *
     * @IsGranted("ROLE_BO_USER")
     */
    public function checkExistanceUserNotifications(
        BoUserNotificationRepository $boUserNotificationRepository
    ): JsonResponse {
        if ($this->isGranted('ROLE_BO_USER')){
            return new JsonResponse([
                'hasNotifs' => $boUserNotificationRepository->checkExistanceNotifsByUser($this->userContext->getUser()),
            ]);
        } else {
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }
    }

    /**
     * Marque toutes les notifications d'un user lues.
     * L'id correspond au SocieteUser pour lequel récupérer les notifications
     * (doit correspondre à un SocieteUser lié à l'user acutellement connecté)
     *
     * @Route(
     *      "/{id}",
     *      methods={"POST"},
     *      name="bo_api_user_notifications_acknowledge"
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
