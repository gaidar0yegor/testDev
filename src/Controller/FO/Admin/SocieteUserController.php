<?php

namespace App\Controller\FO\Admin;

use App\Entity\SocieteUser;
use App\Form\InviteUserType;
use App\Form\SocieteUserType;
use App\Repository\SocieteUserActivityRepository;
use App\Repository\SocieteUserRepository;
use App\Security\Voter\SameSocieteVoter;
use App\Service\Invitator;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @Route("/utilisateurs")
 */
class SocieteUserController extends AbstractController
{
    /**
     * @Route("", name="app_fo_admin_utilisateurs")
     */
    public function listerUtilisateurs(SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        return $this->render('utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'societeUsers' => $societeUserRepository->findBySameSociete($userContext->getSocieteUser()),
        ]);
    }

    /**
     * @Route("/invite", name="app_fo_admin_user_invite")
     */
    public function invite(
        Request $request,
        UserContext $userContext,
        EntityManagerInterface $em,
        Invitator $invitator
    ): Response {
        $societeUser = $invitator->initUser($userContext->getSocieteUser()->getSociete());
        $form = $this->createForm(InviteUserType::class, $societeUser);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitator->check($societeUser);
            $invitator->sendInvitation($societeUser, $this->getUser());
            $em->flush();

            $this->addFlash('success', 'Un lien d\'invitation a été envoyé.');

            return $this->redirectToRoute('app_fo_admin_user_invite');
        }

        return $this->render('utilisateurs_fo/invite_user.html.twig', [
            'form' => $form->createView(),
            'bouton' => 'Inviter',
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="app_fo_admin_utilisateur_modifier")
     */
    public function modifier(Request $request, SocieteUser $societeUser, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        $form = $this->createForm(SocieteUserType::class, $societeUser);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($societeUser);
            $em->flush();

            $this->addFlash('success', 'Les informations de l\'utilisateur ont été modifiées');

            return $this->redirectToRoute('app_fo_societe_user', [
                'id' => $societeUser->getId(),
            ]);
        }

        return $this->render('utilisateurs_fo/edit_user.html.twig', [
            'form' => $form->createView(),
            'societeUser' => $societeUser,
        ]);
    }

    /**
     * @Route(
     *      "/{id}/desactiver",
     *      name="utilisateur_fo_disable",
     *      methods={"POST"}
     * )
     */
    public function disable(SocieteUser $societeUser, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        if (!$societeUser->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur a déjà été désactivé.');
        }

        $societeUser->setEnabled(false);

        $em->persist($societeUser);
        $em->flush();

        $this->addFlash('warning', sprintf(
            'L\'utilisateur %s a été désactivé, il ne pourra plus se connecter.',
            $societeUser->getUser()->getFullname()
        ));

        return $this->redirectToRoute('app_fo_admin_utilisateur_modifier', [
            'id' => $societeUser->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/{id}/activer",
     *      name="utilisateur_fo_enable",
     *      methods={"POST"}
     * )
     */
    public function enable(Request $request, SocieteUser $societeUser, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        if ($societeUser->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur est déjà activé.');
        }

        $societeUser->setEnabled(true);

        $em->persist($societeUser);
        $em->flush();

        $this->addFlash('success', sprintf(
            'L\'utilisateur %s a été activé, il pourra se connecter de nouveau.',
            $societeUser->getUser()->getFullname()
        ));

        return $this->redirectToRoute('app_fo_admin_utilisateur_modifier', [
            'id' => $societeUser->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/{id}/activite",
     *      name="app_fo_admin_utilisateur_activity"
     * )
     */
    public function activity(SocieteUser $societeUser, SocieteUserActivityRepository $societeUserActivityRepository)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        return $this->render('utilisateurs_fo/user_activity.html.twig', [
            'societeUser' => $societeUser,
            'activities' => $societeUserActivityRepository->findBySocieteUser($societeUser),
        ]);
    }
}
