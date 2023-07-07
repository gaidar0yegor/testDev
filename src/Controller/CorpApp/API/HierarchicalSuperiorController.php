<?php

namespace App\Controller\CorpApp\API;

use App\Entity\SocieteUser;
use App\File\FileHandler\AvatarHandler;
use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/api/equipe")
 */
class HierarchicalSuperiorController extends AbstractController
{
    private UserContext $userContext;
    private TranslatorInterface $translator;
    private NormalizerInterface $normalizer;
    private AvatarHandler $avatarHandler;

    public function __construct(
        UserContext $userContext,
        TranslatorInterface $translator,
        NormalizerInterface $normalizer,
        AvatarHandler $avatarHandler
    )
    {
        $this->userContext = $userContext;
        $this->translator = $translator;
        $this->normalizer = $normalizer;
        $this->avatarHandler = $avatarHandler;
    }

    /**
     * @Route(
     *      "/organigramme/{id}",
     *      methods={"GET"},
     *      name="api_get_user_team_organigramme"
     * )
     */
    public function organigramme(SocieteUser $societeUser)
    {
        $data = $this->normalizer->normalize($societeUser,'json', [
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            'groups' => 'organigramme'
        ]);

        return new JsonResponse([
            "data" => $data,
            "avatarPublicUrl" => $this->avatarHandler->getPublicUrl(),
        ]);
    }

}
