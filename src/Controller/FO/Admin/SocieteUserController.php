<?php

namespace App\Controller\FO\Admin;

use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\Form\InviteUserType;
use App\Form\SocieteUserProjetsRolesType;
use App\Form\SocieteUserType;
use App\Repository\SocieteUserActivityRepository;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleProjet;
use App\Security\Voter\SameSocieteVoter;
use App\Service\EnableDisableSocieteUserChecker;
use App\Service\Invitator;
use App\MultiSociete\UserContext;
use App\Service\UserProjetAffectation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        $societeUser->getLastSocieteUserPeriod()->setDateEntry(new \DateTime());
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

        $projetsAsCdp = $em->getRepository(ProjetParticipant::class)->findBySocieteUserAndRole($societeUser,RoleProjet::CDP);

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
            'projetsAsCdp' => $projetsAsCdp,
        ]);
    }

    /**
     * @Route(
     *      "/{id}/desactiver",
     *      name="utilisateur_fo_disable",
     *      methods={"POST"}
     * )
     */
    public function disable(
        Request $request,
        TranslatorInterface $translator,
        SocieteUser $societeUser,
        UserContext $userContext,
        EntityManagerInterface $em,
        EnableDisableSocieteUserChecker $enableDisableSocieteUserChecker
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        if (!$this->isCsrfTokenValid('disable_user_'.$societeUser->getId(), $request->get('csrf_token'))) {
            $this->addFlash('danger', $translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('app_fo_admin_utilisateur_modifier', [
                'id' => $societeUser->getId(),
            ]);
        }

        if ($userContext->getSocieteUser() === $societeUser) {
            throw new ConflictHttpException($translator->trans('cannot_disable_self'));
        }

        if (!$societeUser->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur a déjà été désactivé.');
        }

        if (!$enableDisableSocieteUserChecker->canDisable($societeUser)){

            $this->addFlash('warning', $translator->trans('verif_date_leave_on_disable_user', [
                'user' => $societeUser->getUser()->getFullname(),
            ]));

            return $this->redirectToRoute('app_fo_admin_utilisateur_modifier', [
                'id' => $societeUser->getId(),
            ]);
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
    public function enable(
        Request $request,
        TranslatorInterface $translator,
        SocieteUser $societeUser,
        EntityManagerInterface $em,
        EnableDisableSocieteUserChecker $enableDisableSocieteUserChecker
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        if (!$this->isCsrfTokenValid('re_enable_user_'.$societeUser->getId(), $request->get('csrf_token'))) {
            $this->addFlash('danger', $translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('app_fo_admin_utilisateur_modifier', [
                'id' => $societeUser->getId(),
            ]);
        }

        if ($societeUser->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur est déjà activé.');
        }

        if (!$enableDisableSocieteUserChecker->canEnable($societeUser)){

            $this->addFlash('warning', $translator->trans('verif_date_entry_on_enable_user', [
                'user' => $societeUser->getUser()->getFullname(),
            ]));

            return $this->redirectToRoute('app_fo_admin_utilisateur_modifier', [
                'id' => $societeUser->getId(),
            ]);
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
     * @Route("/{id}/supprimer", name="utilisateur_fo_delete")
     */
    public function delete(
        Request $request,
        TranslatorInterface $translator,
        SocieteUser $societeUser,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        if ($request->isMethod('POST')) {

            if (!$this->isCsrfTokenValid('delete_user_'.$societeUser->getId(), $request->get('_token'))) {
                $this->addFlash('danger', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('app_fo_admin_utilisateur_modifier', [
                    'id' => $societeUser->getId(),
                ]);
            }
            $user = $societeUser->getUser();
            $user->setCurrentSocieteUser(null);
            $em->persist($user);
            $em->remove($societeUser);
            $em->flush();

            $this->addFlash('warning', $translator->trans('user_have_been_deleted', [
                'user' => $user->getFullname(),
            ]));

            return $this->redirectToRoute('app_fo_admin_utilisateurs');
        }

        return $this->render('utilisateurs_fo/delete_user.html.twig', [
            'societeUser' => $societeUser,
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

    /**
     * @Route(
     *      "/{id}/roles-projets",
     *      name="app_fo_admin_utilisateur_roles_projets"
     * )
     */
    public function rolesProjets(
        Request $request,
        SocieteUser $societeUser,
        EntityManagerInterface $em,
        UserProjetAffectation $userProjetAffectation
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        $userProjetAffectation->addProjetsWithNoRole($societeUser);

        $form = $this->createForm(SocieteUserProjetsRolesType::class, $societeUser);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProjetAffectation->clearProjetsWithNoRole($societeUser);

            $em->flush();

            $this->addFlash('success', sprintf(
                'Les rôles de %s sur les projets ont été mis à jour.',
                $societeUser->getUser()->getFullnameOrEmail()
            ));

            return $this->redirectToRoute('app_fo_admin_utilisateur_roles_projets', [
                'id' => $societeUser->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash(
                'danger',
                'Les rôles n\'ont pas été mis à jour à cause d\'une incohérence, vous pouvez revérifier'
            );
        }

        return $this->render('utilisateurs_fo/roles_projets.html.twig', [
            'societeUser' => $societeUser,
            'form' => $form->createView(),
        ]);
    }
}
