<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mes-societes")
 */
class MultiSocieteController extends AbstractController
{
    /**
     * @Route("", name="corp_app_fo_multi_societe_switch")
     */
    public function switch(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response
    {
        $userContext->disconnectSociete();
        $em->flush();
        return $this->render('corp_app/multi_societe/switch.html.twig');
    }

    /**
     * @Route(
     *      "/{id}",
     *      requirements={"id": "\d+"},
     *      methods={"POST"},
     *      name="corp_app_fo_multi_societe_switch_post"
     * )
     */
    public function switchPost(
        SocieteUser $societeUser,
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $userContext->switchSociete($societeUser);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route(
     *      "/deconnexion",
     *      methods={"POST"},
     *      name="corp_app_fo_multi_societe_switch_disconnect"
     * )
     */
    public function switchQuit(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $userContext->disconnectSociete();
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/projets",name="corp_app_fo_multi_societe_projets")
     */
    public function projets(): Response
    {
        return $this->render('corp_app/multi_societe/liste_projets.html.twig');
    }
}