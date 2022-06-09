<?php

namespace App\Controller\CorpApp\API;

use App\Entity\User;
use App\Exception\RdiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class OnboardingController extends AbstractController
{
    /**
     * Créer ou met à jour un Cra.
     *
     * @Route(
     *      "/onboarding/close",
     *      methods={"POST"},
     *      name="api_onboarding_close"
     * )
     */
    public function close(EntityManagerInterface $em, SessionInterface $session)
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new RdiException('Unexpected user instance');
        }

        $user->setOnboardingEnabled(false);
        $session->remove('onboardingSteps');

        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
