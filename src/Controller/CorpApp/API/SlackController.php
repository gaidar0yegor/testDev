<?php

namespace App\Controller\CorpApp\API;

use App\Entity\SlackAccessToken;
use App\Security\Voter\SameSocieteVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("SOCIETE_ADMIN")
 * @Route("/api")
 */
class SlackController extends AbstractController
{
    /**
     * Supprime un access token Slack
     * pour ne plus envoyer de notification en utilisant ce token.
     *
     * @Route(
     *      "/slack/remove-token/{id}",
     *      methods={"POST"},
     *      name="api_slack_remove_token"
     * )
     */
    public function removeToken(SlackAccessToken $slackAccessToken, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $slackAccessToken);

        $em->remove($slackAccessToken);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
