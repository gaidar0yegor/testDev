<?php

namespace App\Controller\LabApp\FO\Admin;

use App\Form\LabApp\UserBookInvitationType;
use App\Repository\LabApp\UserBookRepository;
use App\MultiSociete\UserContext;
use App\Service\LabApp\Invitator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/utilisateurs")
 */
class UserBookController extends AbstractController
{
    /**
     * @Route("", name="lab_app_fo_admin_utilisateurs")
     */
    public function listerUtilisateurs(
        UserBookRepository $userBookRepository,
        UserContext $userContext
    )
    {
        return $this->render('lab_app/utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'userBooks' => $userBookRepository->findBySameLabo($userContext->getUserBook()->getLabo()),
        ]);
    }

    /**
     * @Route("/invite", name="lab_app_fo_admin_user_invite")
     * @throws \App\Exception\RdiException
     */
    public function invite(
        Request $request,
        UserContext $userContext,
        EntityManagerInterface $em,
        Invitator $invitator
    ): Response {
        $userBookInvitation = $invitator->initUserBookInvite($userContext->getUserBook()->getLabo());
        $form = $this->createForm(UserBookInvitationType::class, $userBookInvitation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $invitator->check($userBookInvitation);
            $invitator->sendInvitation($userBookInvitation, $this->getUser());
            $em->flush();

            $this->addFlash('success', 'Un lien d\'invitation a été envoyé.');

            return $this->redirectToRoute('lab_app_fo_admin_user_invite');
        }

        return $this->render('lab_app/utilisateurs_fo/invite_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
