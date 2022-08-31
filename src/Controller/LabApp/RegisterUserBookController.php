<?php

namespace App\Controller\LabApp;

use App\Entity\LabApp\Labo;
use App\Entity\LabApp\UserBook;
use App\Entity\User;
use App\RegisterLabo\Checker\CheckRegistredLabo;
use App\RegisterLabo\Form\LaboType;
use App\RegisterLabo\Form\UserBookType;
use App\RegisterLabo\RegisterUserBook;
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

class RegisterUserBookController extends AbstractController
{
    private RegisterUserBook $registerUserBook;

    public function __construct(RegisterUserBook $registerUserBook)
    {
        $this->registerUserBook = $registerUserBook;
    }

    /**
     * @Route("/creer-mon-cahier-labo", name="lab_app_register")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('lab_app_register_user_book');
    }

    /**
     * @Route("/creer-mon-cahier-labo/mon-cahier-labo", name="lab_app_register_user_book")
     */
    public function userBook(Request $request): Response
    {
        $this->registerUserBook->initializeCurrentRegistration();
        $userBook = $this->registerUserBook->getCurrentRegistration()->userBook ?? new UserBook();
        $form = $this->createForm(UserBookType::class, $userBook);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerUserBook->getCurrentRegistration();
            $registration->userBook = $userBook;

            return $this->redirectToRoute('lab_app_register_account');
        }

        return $this->render('lab_app/register/user-book.html.twig', [
            'step' => 1,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-mon-cahier-labo/mon-compte", name="lab_app_register_account")
     */
    public function account(): Response
    {
        return $this->render('lab_app/register/account.html.twig', [
            'step' => 2,
            'userBook' => $this->registerUserBook->getCurrentRegistration()->userBook,
        ]);
    }

    /**
     * @Route("/creer-mon-cahier-labo/mon-compte/creer", name="lab_app_register_account_creation")
     */
    public function accountCreation(
        Request $request,
        MailerInterface $mailer,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $admin = $this->registerUserBook->getCurrentRegistration()->admin ?? new User();
        $form = $this->createForm(AccountType::class, $admin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerUserBook->getCurrentRegistration();
            $admin->setPassword($passwordEncoder->encodePassword($admin, $admin->getPassword()));
            $admin->setCguCgvAcceptedAt(new DateTime());
            $registration->admin = $admin;
            $this->registerUserBook->updateVerificationCode($registration);

            $email = $this->registerUserBook->createVerificationCodeEmail($registration);

            $mailer->send($email);

            return $this->redirectToRoute('lab_app_register_account_verification');
        }

        return $this->render('lab_app/register/account-creation.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
            'userBook' => $this->registerUserBook->getCurrentRegistration()->userBook,
        ]);
    }

    /**
     * @Route("/creer-mon-cahier-labo/mon-compte/verification", name="lab_app_register_account_verification")
     */
    public function accountVerification(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        GuardAuthenticatorHandler $authenticator,
        UserContext $userContext
    ): Response {
        $form = $this->createForm(AccountVerificationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerUserBook->getCurrentRegistration();
            $code = $form->getData()['verificationCode'];

            if ($code === $registration->verificationCode) {
                $userBook = $this->registerUserBook->persistRegistrationUserBook($registration);
                $em->flush();

                $admin = $registration->admin;
                $token = new UsernamePasswordToken($admin, $admin->getPassword(), 'fo', $admin->getRoles());

                $authenticator->authenticateWithToken($token, $request);

                $userContext->switchUserBook($userBook);
                $em->flush();

                $this->addFlash('success', $translator->trans('Votre compte a été créée avec succès !'));

                $this->registerUserBook->initializeCurrentRegistration();

                return $this->redirectToRoute('lab_app_register_labo');
            }

            $this->addFlash('error', $translator->trans('Le code n\'est pas valide.'));
        }

        return $this->render('lab_app/register/account-verification.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-mon-cahier-labo/mon-compte/rejoindre", name="lab_app_register_account_join")
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function accountJoin(
        Request $request,
        UserContext $userContext,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $registration = $this->registerUserBook->getCurrentRegistration();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('register_join_labo', $request->get('csrf_token'))) {
                $this->addFlash('error', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('lab_app_register_account_join');
            }

            $registration->admin = $userContext->getUser();
            $userBook = $this->registerUserBook->persistRegistrationUserBook($registration);

            $userContext->switchUserBook($userBook);
            $em->flush();

            $this->registerUserBook->initializeCurrentRegistration();

            return $this->redirectToRoute('lab_app_register_labo');
        }

        return $this->render('lab_app/register/account-join.html.twig', [
            'step' => 2,
            'userBook' => $registration->userBook,
        ]);
    }

    /**
     * @Route("/creer-mon-cahier-labo/mon-laboratoire", name="lab_app_register_labo")
     */
    public function labo(
        Request $request,
        EntityManagerInterface $em,
        CheckRegistredLabo $checkRegistredLabo,
        UserContext $userContext
    ): Response {
        $labo = $this->registerUserBook->getCurrentRegistration()->labo ?? new Labo();
        $form = $this->createForm(LaboType::class, $labo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerUserBook->getCurrentRegistration();
            $userBook = $userContext->getUserBook();
            $labo = $checkRegistredLabo->checkLabo($userBook, $form);

            if (null !== $labo){
                $registration->labo = $labo;
                $registration->userBook = $userBook;
                $this->registerUserBook->persistRegistrationLabo($registration);
                $em->flush();

                return $this->redirectToRoute('lab_app_register_finish');
            }
        }

        return $this->render('lab_app/register/labo.html.twig', [
            'step' => 3,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-mon-cahier-labo/inscription-terminee", name="lab_app_register_finish")
     */
    public function finish(): Response
    {
        return $this->render('lab_app/register/finish.html.twig', [
            'step' => 4,
        ]);
    }
}
