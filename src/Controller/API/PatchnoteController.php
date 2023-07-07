<?php

namespace App\Controller\API;

use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class PatchnoteController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserContext $userContext;

    public function __construct(
        EntityManagerInterface $em,
        UserContext $userContext
    )
    {
        $this->em = $em;
        $this->userContext = $userContext;
    }

    /**
     * @Route(
     *     "/patchnote/readed",
     *     methods={"POST"},
     *     name="api_patchnote_readed"
     * )
     */
    public function readed()
    {
        if (!$this->userContext->getUser()){
            return new JsonResponse([
                'message' => 'Une erreur est survenue !!'
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $user = $this->userContext->getUser();
        if (!$user->getPatchnoteReaded()){
            $user->setPatchnoteReaded(true);
            $this->em->persist($user);
            $this->em->flush();

            return new JsonResponse([
                "readed" => true,
            ]);
        }

        return new JsonResponse([
            'message' => 'Veuillez patienter !!'
        ]);
    }
}
