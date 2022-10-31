<?php

namespace App\Controller\LabApp\FO;

use App\Entity\LabApp\UserBook;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mes-cahiers-labo")
 */
class MultiUserBookController extends AbstractController
{
    /**
     * @Route("", name="lab_app_fo_multi_user_book_switch")
     */
    public function switch(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response
    {
        $userContext->disconnectUserLabo();
        $em->flush();
        return $this->render('lab_app/multi_user_book/switch.html.twig');
    }

    /**
     * @Route(
     *      "/{id}",
     *      requirements={"id": "\d+"},
     *      methods={"POST"},
     *      name="lab_app_fo_multi_user_book_switch_post"
     * )
     */
    public function switchPost(
        UserBook $userBook,
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $userContext->switchUserBook($userBook);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route(
     *      "/deconnexion",
     *      methods={"POST"},
     *      name="lab_app_fo_multi_user_book_switch_disconnect"
     * )
     */
    public function switchQuit(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $userContext->disconnectUserLabo();
        $em->flush();

        return $this->redirectToRoute('app_home');
    }
}