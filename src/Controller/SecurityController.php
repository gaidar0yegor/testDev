<?php

namespace App\Controller;

use App\Form\FinalizeInscriptionType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('ROLE_FO_USER')) {
            return $this->redirectToRoute('synthese_fo_');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
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
            throw $this->createNotFoundException('Lien d\'invitation expiré ou invalide.');
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
}
