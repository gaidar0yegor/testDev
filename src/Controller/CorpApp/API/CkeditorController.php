<?php

namespace App\Controller\CorpApp\API;

use App\Entity\User;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/ckeditor")
 */
class CkeditorController extends AbstractController
{
    private UserContext $userContext;

    private EntityManagerInterface $em;

    public function __construct(UserContext $userContext, EntityManagerInterface $em)
    {
        $this->userContext = $userContext;
        $this->em = $em;
    }

    /**
     * @Route(
     *      "/users/{searchQuery}",
     *      methods={"GET"},
     *      name="api_ckeditor_users_get"
     * )
     */
    public function getUsers(string $searchQuery)
    {
        $users = $this->em->getRepository(User::class)->findMentionedUserBySociete($this->userContext->getSocieteUser()->getSociete() ,$searchQuery);

        return new JsonResponse($users);
    }
}
