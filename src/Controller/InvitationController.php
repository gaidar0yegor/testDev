<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\InvitationTokenAlreadyHasSocieteException;
use App\Exception\InvitationTokenExpiredException;
use App\Form\FinalizeInscriptionType;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Pages pour répondre à une invitation à une société,
 * et se créer un compte.
 */
class InvitationController extends AbstractController
{
    /**
     * @Route("/inscription/{token}", name="app_fo_user_finalize_inscription")
     */
    public function finalizeInscription(
        string $token,
        SocieteUserRepository $societeUserRepository
    ) {
        $societeUser = $societeUserRepository->findOneByInvitationToken($token);

        if (null === $societeUser) {
            throw new InvitationTokenExpiredException();
        }

        if (null !== $this->getUser()) {
            if (count($societeUserRepository->findBy(['societe' => $societeUser->getSociete(), 'user' => $this->getUser()])) > 0){
                throw new InvitationTokenAlreadyHasSocieteException();
            }

            return $this->redirectToRoute('app_fo_user_invitation_join_societe', [
                'token' => $token,
            ]);
        }

        return $this->render('invitation/finalize_inscription.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }

    /**
     * @Route("/inscription/rejoindre-la-societe/{token}", name="app_fo_user_invitation_join_societe")
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function joinSociete(
        Request $request,
        string $token,
        SocieteUserRepository $societeUserRepository,
        UserContext $userContext,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        $societeUser = $societeUserRepository->findOneByInvitationToken($token);

        if (null === $societeUser) {
            throw new InvitationTokenExpiredException();
        }

        if (count($societeUserRepository->findBy(['societe' => $societeUser->getSociete(), 'user' => $this->getUser()])) > 0){
            throw new InvitationTokenAlreadyHasSocieteException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('invitation_join_societe', $request->get('csrf_token'))) {
                $this->addFlash('danger', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('app_fo_user_invitation_rejoindre', [
                    'token' => $token,
                ]);
            }

            $societeUser
                ->setUser($this->getUser())
                ->removeInvitationToken()
            ;

            $userContext->switchSociete($societeUser);

            $em->flush();

            $this->addFlash('success', $translator->trans('Vous avez rejoint la société !'));

            return $this->redirectToRoute('app_home');
        }

        return $this->render('invitation/join_societe.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }
}
