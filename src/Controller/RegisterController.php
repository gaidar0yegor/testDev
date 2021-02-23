<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\User;
use App\Exception\UnexpectedUserException;
use App\RegisterSociete\DTO\InviteCollaborators;
use App\RegisterSociete\Form\AccountType;
use App\RegisterSociete\Form\AccountVerificationType;
use App\RegisterSociete\Form\CollaboratorsType;
use App\RegisterSociete\Form\ProjetType;
use App\RegisterSociete\Form\SocieteType;
use App\RegisterSociete\InviteCollaboratorsService;
use App\RegisterSociete\RegisterSociete;
use App\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RegisterController extends AbstractController
{
    private RegisterSociete $registerSociete;

    public function __construct(RegisterSociete $registerSociete)
    {
        $this->registerSociete = $registerSociete;
    }

    /**
     * @Route("/creer-ma-societe", name="app_register")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('app_register_societe');
    }

    /**
     * @Route("/creer-ma-societe/ma-societe", name="app_register_societe")
     */
    public function societe(Request $request): Response
    {
        if (null !== $redirectResponse = $this->shouldRedirect($request)) {
            return $redirectResponse;
        }

        $societe = $this->registerSociete->getCurrentRegistration()->societe ?? new Societe();
        $form = $this->createForm(SocieteType::class, $societe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerSociete->getCurrentRegistration();
            $registration->societe = $societe;

            return $this->redirectToRoute('app_register_account');
        }

        return $this->render('register/societe.html.twig', [
            'step' => 1,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-compte", name="app_register_account")
     */
    public function account(Request $request, MailerInterface $mailer): Response
    {
        if (null !== $redirectResponse = $this->shouldRedirect($request)) {
            return $redirectResponse;
        }

        $admin = $this->registerSociete->getCurrentRegistration()->admin ?? new User();
        $form = $this->createForm(AccountType::class, $admin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $this->registerSociete->getCurrentRegistration();
            $registration->admin = $admin;
            $this->registerSociete->updateVerificationCode($registration);

            $email = $this->registerSociete->createVerificationCodeEmail($registration);

            $mailer->send($email);

            return $this->redirectToRoute('app_register_account_verification');
        }

        return $this->render('register/account.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
            'societe' => $this->registerSociete->getCurrentRegistration()->societe,
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-compte/verification", name="app_register_account_verification")
     */
    public function accountVerification(
        Request $request,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): Response {
        if (null !== $redirectResponse = $this->shouldRedirect($request)) {
            return $redirectResponse;
        }

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
                $tokenStorage->setToken($token);

                $this->addFlash('success', 'Votre compte a été créée avec succès !');

                return $this->redirectToRoute('app_register_projet');
            }

            $this->addFlash('danger', 'Le code n\'est pas valide.');
        }

        return $this->render('register/account-verification.html.twig', [
            'step' => 2,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/mon-projet", name="app_register_projet")
     */
    public function projet(Request $request, EntityManagerInterface $em): Response
    {
        if (null !== $redirectResponse = $this->shouldRedirect($request)) {
            return $redirectResponse;
        }

        $projet = (new Projet())
            ->setSociete($this->getUser()->getSociete())
        ;
        $participant = (new ProjetParticipant())
            ->setProjet($projet)
            ->setUser($this->getUser())
            ->setRole(Role::CDP)
        ;

        $form = $this->createForm(ProjetType::class, $projet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);
            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'Votre projet a bien été ajouté.');

            return $this->redirectToRoute('app_register_collaborators');
        }

        return $this->render('register/projet.html.twig', [
            'step' => 3,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/inviter-mes-collaborateurs", name="app_register_collaborators")
     */
    public function collaborators(
        Request $request,
        InviteCollaboratorsService $inviteCollaboratorsService,
        EntityManagerInterface $em
    ): Response {
        if (null !== $redirectResponse = $this->shouldRedirect($request)) {
            return $redirectResponse;
        }

        $inviteCollaborators = new InviteCollaborators();
        $form = $this->createForm(CollaboratorsType::class, $inviteCollaborators);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projets = $this->getUser()->getSociete()->getProjets();
            $projet = count($projets) > 0 ? $projets[0] : null;

            $inviteCollaboratorsService->inviteCollaborators(
                $inviteCollaborators,
                $this->getUser(),
                $projet
            );

            $em->flush();

            return $this->redirectToRoute('app_register_finish');
        }

        return $this->render('register/collaborators.html.twig', [
            'step' => 4,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/creer-ma-societe/inscription-terminee", name="app_register_finish")
     */
    public function finish(Request $request): Response
    {
        if (null !== $redirectResponse = $this->shouldRedirect($request)) {
            return $redirectResponse;
        }

        return $this->render('register/finish.html.twig', [
            'step' => 5,
        ]);
    }

    /**
     * Returns possible steps from current registration state.
     *
     * @return string[]
     */
    private function getExpectedRoutes(): array
    {
        if (null === $this->getUser()) {
            if (!$this->registerSociete->hasCurrentRegistration()) {
                $this->registerSociete->initializeCurrentRegistration();
            }

            $registration = $this->registerSociete->getCurrentRegistration();

            if (null === $registration->societe) {
                return ['app_register_societe'];
            }

            if (null === $registration->admin || null === $registration->verificationCode) {
                return ['app_register_account'];
            }

            return ['app_register_account', 'app_register_account_verification'];
        }

        if (!$this->isGranted('ROLE_FO_ADMIN')) {
            return ['app_home'];
        }

        $admin = $this->getUser();

        if (!$admin instanceof User) {
            throw new UnexpectedUserException($admin);
        }

        $routes = [];

        if (0 === count($admin->getSociete()->getProjets())) {
            $routes[] = 'app_register_projet';
        }

        if (count($admin->getSociete()->getUsers()) < 2) {
            $routes[] = 'app_register_collaborators';
        }

        $routes[] = 'app_register_finish';

        return $routes;
    }

    /**
     * Returns a redirect response to another step if current step is not yet
     * available or already completed.
     */
    private function shouldRedirect(Request $request): ?Response
    {
        $expectedRoutes = $this->getExpectedRoutes();
        $actualRoute = $request->attributes->get('_route');

        // Stay on this step if expected
        if (in_array($actualRoute, $expectedRoutes)) {
            return null;
        }

        $workflow = [
            'app_register_societe',
            'app_register_account',
            'app_register_account_verification',
            'app_register_projet',
            'app_register_collaborators',
            'app_register_finish',
        ];

        $current = array_search($actualRoute, $workflow);

        // Redirect to the next incompleted step
        for ($i = $current + 1; $i < count($workflow); ++$i) {
            if (in_array($workflow[$i], $expectedRoutes)) {
                return $this->redirectToRoute($workflow[$i]);
            }
        }

        // Or redirect to a previous missing step
        return $this->redirectToRoute($expectedRoutes[0]);
    }
}
