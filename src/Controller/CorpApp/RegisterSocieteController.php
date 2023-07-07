<?php

namespace App\Controller\CorpApp;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\User;
use App\RegisterSociete\DTO\InviteCollaborators;
use App\RegisterSociete\Form\AccountType;
use App\RegisterSociete\Form\AccountVerificationType;
use App\RegisterSociete\Form\CollaboratorsType;
use App\RegisterSociete\Form\ProjetType;
use App\RegisterSociete\Form\SocieteType;
use App\RegisterSociete\InviteCollaboratorsService;
use App\RegisterSociete\RegisterSociete;
use App\Security\Role\RoleProjet;
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

class RegisterSocieteController extends AbstractController
{
    private RegisterSociete $registerSociete;

    public function __construct(RegisterSociete $registerSociete)
    {
        $this->registerSociete = $registerSociete;
    }

    /**
     * @Route("/creer-ma-societe", name="corp_app_register")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('corp_app_register_societe');
    }

    /**
     * @Route("/creer-ma-societe/ma-societe", name="corp_app_register_societe")
     */
    public function societe(Request $request): Response
    {
        $societe = $this->registerSociete->getCurrentRegistration()->societe ?? new Societe();
        $form = $this->createForm(SocieteType::class, $societe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerSociete->getCurrentRegistration();
            $registration->societe = $societe;

            return $this->redirectToRoute('corp_app_register_account');
        }

        return $this->render('corp_app/register/societe.html.twig', [
            'step' => 1,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-compte", name="corp_app_register_account")
     */
    public function account(): Response
    {
        return $this->render('corp_app/register/account.html.twig', [
            'step' => 2,
            'societe' => $this->registerSociete->getCurrentRegistration()->societe,
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-compte/creer", name="corp_app_register_account_creation")
     */
    public function accountCreation(
        Request $request,
        MailerInterface $mailer,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $admin = $this->registerSociete->getCurrentRegistration()->admin ?? new User();
        $form = $this->createForm(AccountType::class, $admin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerSociete->getCurrentRegistration();
            $admin->setPassword($passwordEncoder->encodePassword($admin, $admin->getPassword()));
            $admin->setCguCgvAcceptedAt(new DateTime());
            $registration->admin = $admin;
            $this->registerSociete->updateVerificationCode($registration);

            $email = $this->registerSociete->createVerificationCodeEmail($registration);

            $mailer->send($email);

            return $this->redirectToRoute('corp_app_register_account_verification');
        }

        return $this->render('corp_app/register/account-creation.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
            'societe' => $this->registerSociete->getCurrentRegistration()->societe,
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-compte/verification", name="corp_app_register_account_verification")
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
            $registration = $this->registerSociete->getCurrentRegistration();
            $code = $form->getData()['verificationCode'];

            if ($code === $registration->verificationCode) {
                $this->registerSociete->persistRegistration($registration);
                $em->flush();

                $admin = $registration->admin;
                $token = new UsernamePasswordToken($admin, $admin->getPassword(), 'fo', $admin->getRoles());

                $authenticator->authenticateWithToken($token, $request);

                $this->addFlash('success', $translator->trans('Votre compte a été créée avec succès !'));

                $this->registerSociete->initializeCurrentRegistration();

                return $this->redirectToRoute('corp_app_register_projet');
            }

            $this->addFlash('error', $translator->trans('Le code n\'est pas valide.'));
        }

        return $this->render('corp_app/register/account-verification.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-compte/rejoindre", name="corp_app_register_account_join")
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function accountJoin(
        Request $request,
        UserContext $userContext,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $registration = $this->registerSociete->getCurrentRegistration();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('register_join_societe', $request->get('csrf_token'))) {
                $this->addFlash('error', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('corp_app_register_account_join');
            }

            $registration->admin = $userContext->getUser();
            $societeUser = $this->registerSociete->persistRegistration($registration);

            $userContext->switchSociete($societeUser);
            $em->flush();

            $this->registerSociete->initializeCurrentRegistration();

            return $this->redirectToRoute('corp_app_register_projet');
        }

        return $this->render('corp_app/register/account-join.html.twig', [
            'step' => 2,
            'societe' => $registration->societe,
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-projet", name="corp_app_register_projet")
     *
     * @IsGranted("SOCIETE_ADMIN")
     */
    public function projet(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UserContext $userContext
    ): Response {
        $projet = (new Projet())
            ->setSociete($userContext->getSocieteUser()->getSociete())
        ;
        $participant = (new ProjetParticipant())
            ->setProjet($projet)
            ->setSocieteUser($userContext->getSocieteUser())
            ->setRole(RoleProjet::CDP)
        ;

        $form = $this->createForm(ProjetType::class, $projet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);
            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', $translator->trans('Votre projet a bien été ajouté.'));

            return $this->redirectToRoute('corp_app_register_collaborators');
        }

        return $this->render('corp_app/register/projet.html.twig', [
            'step' => 3,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/inviter-mes-collaborateurs", name="corp_app_register_collaborators")
     *
     * @IsGranted("SOCIETE_ADMIN")
     */
    public function collaborators(
        Request $request,
        InviteCollaboratorsService $inviteCollaboratorsService,
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $inviteCollaborators = new InviteCollaborators();
        $form = $this->createForm(CollaboratorsType::class, $inviteCollaborators);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projets = $userContext->getSocieteUser()->getSociete()->getProjets();
            $projet = count($projets) > 0 ? $projets[0] : null;

            $inviteCollaboratorsService->inviteCollaborators(
                $inviteCollaborators,
                $userContext->getSocieteUser(),
                $projet
            );

            $em->flush();

            return $this->redirectToRoute('corp_app_register_finish');
        }

        return $this->render('corp_app/register/collaborators.html.twig', [
            'step' => 4,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/inscription-terminee", name="corp_app_register_finish")
     *
     * @IsGranted("SOCIETE_ADMIN")
     */
    public function finish(): Response
    {
        return $this->render('corp_app/register/finish.html.twig', [
            'step' => 5,
        ]);
    }
}
