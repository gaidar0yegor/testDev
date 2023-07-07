<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\ResetPasswordException;
use App\Form\Custom\RepeatedPasswordType;
use App\Form\FinalizeInscriptionType;
use App\Service\ResetPasswordService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private PhoneNumberUtil $phoneNumberUtil;

    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $trans): Response
    {
        if ($this->isGranted('ROLE_FO_USER')) {
            return $this->redirectToRoute('corp_app_fo_dashboard');
        }

        if ($this->isGranted('ROLE_BO_USER')) {
            return $this->redirectToRoute('corp_app_bo_home');
        }

        // get the login error if there is one
        if ($error = $authenticationUtils->getLastAuthenticationError()) {
            $this->addFlash('error', $trans->trans(
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
     * @Route("/inscription", name="app_signup")
     */
    public function signup(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $authenticator,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        if ($request->get('user_telephone')){
            try {
                $phoneNumber = $this->phoneNumberUtil->parse($request->get('user_telephone'));
            } catch (NumberParseException $e) {
                $this->addFlash('error', 'Le numéro de téléphone semble invalide : ' . $e->getMessage());
            }
        }
        $user = new User();
        $user
            ->setPrenom($request->get('user_prenom', ''))
            ->setNom($request->get('user_nom', ''))
            ->setEmail($request->get('user_email'))
            ->setTelephone(isset($phoneNumber) ? $phoneNumber : null)
            ->setCguCgvAcceptedAt(new DateTime())
        ;

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

            $this->addFlash('success', $translator->trans('Bienvenue à {user} !', [
                'user' => $user->getPrenom(),
            ]));

            if ($redirect = $request->get('_redirect')) {
                return $this->redirect($redirect);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/create_account.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reinitialiser-mot-de-passe", name="app_fo_reset_password_request")
     */
    public function resetPasswordRequest(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ResetPasswordService $resetPasswordService
    ) {
        $form = $this->createFormBuilder()
            ->add('username', null, [
                'label' => 'username_or_phone',
            ])
            ->add('Submit', SubmitType::class, [
                'label' => 'request_reset_password_link',
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $resetPasswordService->requestLink($form->getData()['username']);

                $em->flush();

                $this->addFlash('success', $translator->trans(
                    'Un lien de réinitialisation de mot de passe vous a été envoyé.'
                ));

                return $this->redirectToRoute('app_home');
            } catch (ResetPasswordException $e) {
                $form->get('username')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('security/reset_password_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reinitialiser-mot-de-passe/{token}", name="app_fo_reset_password")
     */
    public function resetPassword(
        Request $request,
        string $token,
        ResetPasswordService $resetPasswordService,
        UserPasswordEncoderInterface $passwordEncoder,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        try {
            $user = $resetPasswordService->checkToken($token);
        } catch (ResetPasswordException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $form = $this->createFormBuilder()
            ->add('password', RepeatedPasswordType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'validate_my_password',
            ])
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

            $this->addFlash('success', $translator->trans(
                'Votre mot de passe a été changé. Vous pouvez maintenant vous connecter avec.'
            ));

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
