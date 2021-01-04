<?php

namespace App\Controller\FO\Admin;

use App\Entity\User;
use App\Form\InviteUserType;
use App\Form\UtilisateursFormType;
use App\Repository\UserRepository;
use App\Service\Invitator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @Route("/utilisateurs")
 */
class UtilisateursFoController extends AbstractController
{
    /**
     * @Route("", name="app_fo_admin_utilisateurs")
     */
    public function listerUtilisateurs(UserRepository $ur)
    {
        return $this->render('utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'users' => $ur->findBySameSociete($this->getUser()),
        ]);
    }

    /**
     * @Route("/invite", name="app_fo_admin_user_invite")
     */
    public function invite(
        Request $request,
        EntityManagerInterface $em,
        Invitator $invitator
    ): Response {
        $user = $invitator->initUser($this->getUser()->getSociete());
        $form = $this->createForm(InviteUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitator->check($user);
            $invitator->sendInvitation($user, $this->getUser());
            $em->flush();

            $this->addFlash('success', sprintf(
                'Un email avec un lien d\'invitation a été envoyé à "%s".',
                $user->getEmail()
            ));

            return $this->redirectToRoute('app_fo_admin_user_invite');
        }

        return $this->render('utilisateurs_fo/invite_user.html.twig', [
            'form' => $form->createView(),
            'bouton' => 'Inviter',
        ]);
    }

    /**
     * @Route("/{id}", name="app_fo_admin_user")
     */
    public function compteUtilisateur(User $user)
    {
        $this->denyAccessUnlessGranted('same_societe', $user);

        return $this->render('utilisateurs_fo/view_user.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="app_fo_admin_utilisateur_modifier")
     */
    public function modifier(Request $request, User $user, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('same_societe', $user);

        $form = $this->createForm(UtilisateursFormType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Les informations de l\'utilisateur ont été modifiées');

            return $this->redirectToRoute('app_fo_admin_user', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('utilisateurs_fo/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'bouton' => 'Modifier',
        ]);
    }

    /**
     * @Route(
     *      "/{id}/desactiver",
     *      name="utilisateur_fo_disable",
     *      methods={"POST"}
     * )
     */
    public function disable(Request $request, User $user, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('same_societe', $user);

        if (!$user->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur a déjà été désactivé.');
        }

        $user->setEnabled(false);

        $em->persist($user);
        $em->flush();

        $this->addFlash('warning', sprintf(
            'L\'utilisateur %s a été désactivé, il ne pourra plus se connecter.',
            $user->getFullname()
        ));

        return $this->redirectToRoute('app_fo_admin_user', [
            'id' => $user->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/{id}/activer",
     *      name="utilisateur_fo_enable",
     *      methods={"POST"}
     * )
     */
    public function enable(Request $request, User $user, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('same_societe', $user);

        if ($user->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur est déjà activé.');
        }

        $user->setEnabled(true);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', sprintf(
            'L\'utilisateur %s a été activé, il pourra se connecter de nouveau.',
            $user->getFullname()
        ));

        return $this->redirectToRoute('app_fo_admin_user', [
            'id' => $user->getId(),
        ]);
    }
}
