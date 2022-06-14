<?php

namespace App\Controller\LabApp;

use App\Entity\LabApp\Labo;
use App\Entity\User;
use App\RegisterLabo\Form\LaboType;
use App\RegisterLabo\RegisterLabo;
use App\RegisterSociete\Form\AccountType;
use App\RegisterSociete\Form\AccountVerificationType;
use App\MultiSociete\UserContext;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterLaboController extends AbstractController
{
    private RegisterLabo $registerLabo;

    public function __construct(RegisterLabo $registerLabo)
    {
        $this->registerLabo = $registerLabo;
    }

    /**
     * @Route("/creer-mon-labo", name="lab_app_register")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('lab_app_register_labo');
    }

    /**
     * @Route("/creer-mon-labo/mon-labo", name="lab_app_register_labo")
     */
    public function labo(Request $request): Response
    {
        $this->registerLabo->initializeCurrentRegistration();
        $labo = $this->registerLabo->getCurrentRegistration()->labo ?? new Labo();
        $form = $this->createForm(LaboType::class, $labo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerLabo->getCurrentRegistration();
            $registration->labo = $labo;

            return $this->redirectToRoute('lab_app_register_account');
        }

        return $this->render('lab_app/register/labo.html.twig', [
            'step' => 1,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-mon-labo/mon-compte", name="lab_app_register_account")
     */
    public function account(): Response
    {
        return $this->render('lab_app/register/account.html.twig', [
            'step' => 2,
            'labo' => $this->registerLabo->getCurrentRegistration()->labo,
        ]);
    }

    /**
     * @Route("/creer-mon-labo/mon-compte/creer", name="lab_app_register_account_creation")
     */
    public function accountCreation(
        Request $request,
        MailerInterface $mailer,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $admin = $this->registerLabo->getCurrentRegistration()->admin ?? new User();
        $form = $this->createForm(AccountType::class, $admin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerLabo->getCurrentRegistration();
            $admin->setPassword($passwordEncoder->encodePassword($admin, $admin->getPassword()));
            $admin->setCguCgvAcceptedAt(new DateTime());
            $registration->admin = $admin;
            $this->registerLabo->updateVerificationCode($registration);

            $email = $this->registerLabo->createVerificationCodeEmail($registration);

            $mailer->send($email);

            return $this->redirectToRoute('lab_app_register_account_verification');
        }

        return $this->render('lab_app/register/account-creation.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
            'labo' => $this->registerLabo->getCurrentRegistration()->labo,
        ]);
    }

    /**
     * @Route("/creer-mon-labo/mon-compte/verification", name="lab_app_register_account_verification")
     */
    public function accountVerification(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        GuardAuthenticatorHandler $authenticator
    ): Response {
        $form = $this->createForm(AccountVerificationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerLabo->getCurrentRegistration();
            $code = $form->getData()['verificationCode'];

            if ($code === $registration->verificationCode) {
                $this->registerLabo->persistRegistration($registration);
                $em->flush();

                $admin = $registration->admin;
                $token = new UsernamePasswordToken($admin, $admin->getPassword(), 'fo', $admin->getRoles());

                $authenticator->authenticateWithToken($token, $request);

                $this->addFlash('success', $translator->trans('Votre compte a été créée avec succès !'));

                $this->registerLabo->initializeCurrentRegistration();

                return $this->redirectToRoute('lab_app_register_finish');
            }

            $this->addFlash('danger', $translator->trans('Le code n\'est pas valide.'));
        }

        return $this->render('lab_app/register/account-verification.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-mon-labo/mon-compte/rejoindre", name="lab_app_register_account_join")
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function accountJoin(
        Request $request,
        UserContext $userContext,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $registration = $this->registerLabo->getCurrentRegistration();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('register_join_labo', $request->get('csrf_token'))) {
                $this->addFlash('danger', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('lab_app_register_account_join');
            }

            $registration->admin = $userContext->getUser();
            $userBook = $this->registerLabo->persistRegistration($registration);

            $userContext->switchUserBook($userBook);
            $em->flush();

            $this->registerLabo->initializeCurrentRegistration();

            return $this->redirectToRoute('lab_app_register_finish');
        }

        return $this->render('lab_app/register/account-join.html.twig', [
            'step' => 2,
            'labo' => $registration->labo,
        ]);
    }

    /**
     * @Route("/creer-mon-labo/inscription-terminee", name="lab_app_register_finish")
     */
    public function finish(): Response
    {
        return $this->render('lab_app/register/finish.html.twig', [
            'step' => 3,
        ]);
    }
}
