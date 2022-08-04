<?php

namespace App\Controller\LabApp;

use App\Entity\LabApp\UserBook;
use App\Exception\InvitationTokenAlreadyHasLaboException;
use App\Exception\InvitationTokenExpiredException;
use App\MultiSociete\UserContext;
use App\RegisterLabo\Checker\CheckJoinedUserBook;
use App\RegisterLabo\Form\UserBookType;
use App\Repository\LabApp\UserBookInviteRepository;
use App\Repository\LabApp\UserBookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Pages pour répondre à une invitation à un labo, et se créer un compte.
 */
class InvitationController extends AbstractController
{
    /**
     * @Route("/inscription/{token}", name="lab_app_fo_user_finalize_inscription")
     */
    public function finalizeInscription(
        string $token,
        UserBookInviteRepository $userBookInviteRepository,
        UserBookRepository $userBookRepository
    ) {
        $userBookInvite = $userBookInviteRepository->findOneByInvitationToken($token);

        if (null === $userBookInvite) {
            throw new InvitationTokenExpiredException();
        }

        if (null !== $this->getUser()) {
            if (count($userBookRepository->findBy(['labo' => $userBookInvite->getLabo(), 'user' => $this->getUser()])) > 0){
                throw new InvitationTokenAlreadyHasLaboException();
            }

            return $this->redirectToRoute('lab_app_fo_user_invitation_join_labo', [
                'token' => $token,
            ]);
        }

        return $this->render('lab_app/invitation/finalize_inscription.html.twig', [
            'userBookInvite' => $userBookInvite,
        ]);
    }

    /**
     * @Route("/inscription/rejoindre-le-labo/{token}", name="lab_app_fo_user_invitation_join_labo")
     *
     * @IsGranted("ROLE_FO_USER")
     * @throws \App\Exception\RdiException
     */
    public function joinLabo(
        Request $request,
        string $token,
        UserBookInviteRepository $userBookInviteRepository,
        UserBookRepository $userBookRepository,
        CheckJoinedUserBook $checkJoinedUserBook,
        UserContext $userContext,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        $userBookInvite = $userBookInviteRepository->findOneByInvitationToken($token);

        if (null === $userBookInvite) {
            throw new InvitationTokenExpiredException();
        }

        if (count($userBookRepository->findBy(['labo' => $userBookInvite->getLabo(), 'user' => $this->getUser()])) > 0){
            throw new InvitationTokenAlreadyHasLaboException();
        }

        $form = $this->createForm(UserBookType::class, new UserBook(), [
            'user_to_join_labo' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userBook = $checkJoinedUserBook->checkUserBook($userBookInvite, $form);

            if (null !== $userBook) {
                $em->persist($userBook);
                $userContext->switchUserBook($userBook);
                $em->flush();

                $em->remove($userBookInvite);
                $em->flush();

                $this->addFlash('success', $translator->trans('Vous avez rejoint le laboratoire !'));

                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('lab_app/invitation/join_labo.html.twig', [
            'userBookInvite' => $userBookInvite,
            'form' => $form->createView(),
        ]);
    }
}
