<?php

namespace App\Controller;

use App\Exception\ResetPasswordException;
use App\Form\FinalizeInscriptionType;
use App\Repository\UserRepository;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $trans): Response
    {
        if ($this->isGranted('ROLE_FO_USER')) {
            return $this->redirectToRoute('synthese_fo_');
        }

        // get the login error if there is one
        if ($error = $authenticationUtils->getLastAuthenticationError()) {
            $this->addFlash('danger', $trans->trans(
                $error->getMessageKey(),
                $error->getMessageData(),
                'security'
            ));
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/inscription/{token}", name="fo_user_finalize_inscription")
     */
    public function finalizeInscription(
        Request $request,
        string $token,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em
    ) {
        $user = $userRepository->findOneBy([
            'invitationToken' => $token,
        ]);

        if (null === $user) {
            return $this->render(
                'utilisateurs_fo/invalid_invitation_token.html.twig',
                [],
                new Response('', Response::HTTP_NOT_FOUND)
            );
        }

        $form = $this->createForm(FinalizeInscriptionType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());

            $user
                ->setPassword($encodedPassword)
                ->removeInvitationToken()
            ;

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', sprintf('Bienvenue à %s !', $user->getPrenom()));

            return $this->redirectToRoute('projets_');
        }

        return $this->render('utilisateurs_fo/finalize_inscription.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reinitialiser-mot-de-passe", name="app_reset_password_request")
     */
    public function resetPasswordRequest(
        Request $request,
        EntityManagerInterface $em,
        ResetPasswordService $resetPasswordService
    ) {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('Demander un lien de réinitialisation', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $resetPasswordService->requestLink($form->getData()['email']);

                $em->flush();

                $this->addFlash('success', 'Un lien de réinitialisation de mot de passe a été envoyé à votre email.');

                return $this->redirectToRoute('app_home');
            } catch (ResetPasswordException $e) {
                $form->get('email')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('security/reset_password_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reinitialiser-mot-de-passe/{token}", name="app_reset_password")
     */
    public function resetPassword(
        Request $request,
        string $token,
        ResetPasswordService $resetPasswordService,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em
    ) {
        try {
            $user = $resetPasswordService->checkToken($token);
        } catch (ResetPasswordException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Erreur dans l\'un des deux mots de passe.',
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répétez votre mot de passe'],
            ])
            ->add('Valider mon mot de passe', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $passwordEncoder->encodePassword($user, $form->getData()['password']);

            $user
                ->setPassword($encodedPassword)
                ->removeResetPasswordToken()
            ;

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été changé. Vous pouvez maintenant vous connecter avec.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
