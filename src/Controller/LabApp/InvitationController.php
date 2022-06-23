<?php

namespace App\Controller\LabApp;

use App\Exception\InvitationTokenAlreadyHasLaboException;
use App\Exception\InvitationTokenExpiredException;
use App\MultiSociete\UserContext;
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
        UserBookRepository $userBookRepository
    ) {
        $userBook = $userBookRepository->findOneByInvitationToken($token);

        if (null === $userBook) {
            throw new InvitationTokenExpiredException();
        }

        if (null !== $this->getUser()) {
            if (count($userBookRepository->findBy(['labo' => $userBook->getLabo(), 'user' => $this->getUser()])) > 0){
                throw new InvitationTokenAlreadyHasLaboException();
            }

            return $this->redirectToRoute('lab_app_fo_user_invitation_join_labo', [
                'token' => $token,
            ]);
        }

        return $this->render('lab_app/invitation/finalize_inscription.html.twig', [
            'userBook' => $userBook,
        ]);
    }

    /**
     * @Route("/inscription/rejoindre-le-labo/{token}", name="lab_app_fo_user_invitation_join_labo")
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function joinLabo(
        Request $request,
        string $token,
        UserBookRepository $userBookRepository,
        UserContext $userContext,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        $userBook = $userBookRepository->findOneByInvitationToken($token);

        if (null === $userBook) {
            throw new InvitationTokenExpiredException();
        }

        if (count($userBookRepository->findBy(['labo' => $userBook->getLabo(), 'user' => $this->getUser()])) > 0){
            throw new InvitationTokenAlreadyHasLaboException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('invitation_join_labo', $request->get('csrf_token'))) {
                $this->addFlash('danger', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('lab_app_fo_user_invitation_join_labo', [
                    'token' => $token,
                ]);
            }

            $userBook
                ->setUser($this->getUser())
                ->removeInvitationToken()
            ;

            $userContext->switchUserBook($userBook);

            $em->flush();

            $this->addFlash('success', $translator->trans('Vous avez rejoint le laboratoire !'));

            return $this->redirectToRoute('app_home');
        }

        return $this->render('lab_app/invitation/join_labo.html.twig', [
            'userBook' => $userBook,
        ]);
    }
}
