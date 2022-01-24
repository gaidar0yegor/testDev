<?php

namespace App\Controller\FO;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Clue\StreamFilter\fun;

/**
 * @Route("/mes-societes")
 */
class MultiSocieteController extends AbstractController
{
    /**
     * @Route("", name="app_fo_multi_societe_switch")
     */
    public function switch(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response
    {
        $userContext->disconnectSociete();
        $em->flush();
        return $this->render('multi_societe/switch.html.twig');
    }

    /**
     * @Route(
     *      "/{id}",
     *      requirements={"id": "\d+"},
     *      methods={"POST"},
     *      name="app_fo_multi_societe_switch_post"
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
     *      name="app_fo_multi_societe_switch_disconnect"
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
     * @Route("/dashboard/general", name="app_fo_multi_societe_dashboard_general")
     */
    public function dashboardGeneral(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $userContext->disconnectSociete();
        $em->flush();

        return $this->render('multi_societe/dashboard-general.html.twig',[
            'societeUsers' => $userContext->getUser()->getSocieteUsers()
        ]);
    }

    /**
     * @Route("/dashboard/consolide", name="app_fo_multi_societe_dashboard_consolide")
     */
    public function dashboardConsolide(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $userContext->disconnectSociete();
        $em->flush();

        return $this->render('multi_societe/dashboard-general.html.twig',[
            'societeUsers' => $userContext->getUser()->getSocieteUsers()
        ]);
    }

    /**
     * @Route("/projets",name="app_fo_multi_societe_projets")
     */
    public function projets(): Response
    {
        return $this->render('multi_societe/liste_projets.html.twig');
    }
}