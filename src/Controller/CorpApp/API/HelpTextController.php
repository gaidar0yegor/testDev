<?php

namespace App\Controller\CorpApp\API;

use App\HelpText\HelpText;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class HelpTextController extends AbstractController
{
    /**
     * Marque un message d'aide comme compris,
     * et ne l'affiche plus.
     *
     * @Route(
     *      "/help-text/acknowledge",
     *      methods={"POST"},
     *      name="api_help_text_acknowledge"
     * )
     */
    public function acknowledge(Request $request, EntityManagerInterface $em, HelpText $helpText)
    {
        $content = json_decode($request->getContent());

        $helpText->acknowledge($content->helpId, $this->getUser());

        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Revoir un message d'aide
     *
     * @Route(
     *      "/help-text/reactive",
     *      methods={"POST"},
     *      name="api_help_text_reactive"
     * )
     */
    public function reactive(Request $request, EntityManagerInterface $em, HelpText $helpText)
    {
        $content = json_decode($request->getContent());

        $helpText->reactive($content->helpId, $this->getUser());

        $em->flush();

        return new Response($helpText->renderHelp($content->helpId));
    }
}
