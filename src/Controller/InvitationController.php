<?php

namespace App\Controller;

use App\Entity\User;
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
            return $this->render(
                'invitation/invalid_invitation_token.html.twig',
                [],
                new Response('', Response::HTTP_NOT_FOUND)
            );
        }

        if (null !== $this->getUser()) {
            return $this->redirectToRoute('app_fo_user_invitation_join_societe', [
                'token' => $token,
            ]);
        }

        return $this->render('invitation/finalize_inscription.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }

    /**
     * @Route("/inscription/creer-mon-compte/{token}", name="app_fo_user_invitation_create_account")
     */
    public function createAccount(
        Request $request,
        string $token,
        SocieteUserRepository $societeUserRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $authenticator,
        EntityManagerInterface $em
    ) {
        $societeUser = $societeUserRepository->findOneByInvitationToken($token);

        if (null === $societeUser) {
            return $this->render(
                'invitation/invalid_invitation_token.html.twig',
                [],
                new Response('', Response::HTTP_NOT_FOUND)
            );
        }

        $user = new User();
        $user->setEmail($societeUser->getInvitationEmail());
        $form = $this->createForm(FinalizeInscriptionType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encodedPassword);

            $em->persist($user);
            $em->flush();

            $authenticator->authenticateWithToken(
                new UsernamePasswordToken($user, $user->getPassword(), 'fo', $user->getRoles()),
                $request
            );

            $this->addFlash('success', sprintf('Bienvenue à %s !', $user->getPrenom()));

            return $this->redirectToRoute('app_fo_user_invitation_join_societe', [
                'token' => $token,
            ]);
        }

        return $this->render('invitation/create_account.html.twig', [
            'societeUser' => $societeUser,
            'user' => $user,
            'form' => $form->createView(),
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
        EntityManagerInterface $em
    ) {
        $societeUser = $societeUserRepository->findOneByInvitationToken($token);

        if (null === $societeUser) {
            return $this->render(
                'invitation/invalid_invitation_token.html.twig',
                [],
                new Response('', Response::HTTP_NOT_FOUND)
            );
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('invitation_join_societe', $request->get('csrf_token'))) {
                $this->addFlash('danger', 'Erreur, il semblerai que le bouton ait expiré, veuillez réessayer.');

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

            $this->addFlash('success', 'Vous avez rejoint la société !');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('invitation/join_societe.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }
}
