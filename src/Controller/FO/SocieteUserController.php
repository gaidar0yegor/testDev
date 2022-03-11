<?php

namespace App\Controller\FO;

use App\DTO\NullUser;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Form\SocieteUserType;
use App\MultiSociete\UserContext;
use App\Notification\Event\ProjetParticipantRemovedEvent;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleProjet;
use App\Security\Role\RoleSociete;
use App\Security\Voter\TeamManagementVoter;
use App\Security\Voter\SameSocieteVoter;
use App\Service\EnableDisableSocieteUserChecker;
use App\Service\EquipeChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SocieteUserController extends AbstractController
{
    private EquipeChecker $equipeChecker;
    private UserContext $userContext;
    private TranslatorInterface $translator;

    public function __construct(
        EquipeChecker $equipeChecker,
        UserContext $userContext,
        TranslatorInterface $translator
    )
    {
        $this->equipeChecker = $equipeChecker;
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    /**
     * @Route("/utilisateur/{id}", name="app_fo_societe_user")
     */
    public function compteUtilisateur(SocieteUser $societeUser)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        return $this->render('utilisateurs_fo/view_user.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }

    /**
     * @Route("/utilisateurs", name="app_fo_utilisateurs")
     */
    public function listerUtilisateurs(SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $userContext->getSocieteUser());

        $isTeamUsers = false;

        if ($this->isGranted(RoleSociete::ADMIN)){
            $societeUsers = $societeUserRepository->findBySameSociete($userContext->getSocieteUser());
        } else {
            $societeUsers = $societeUserRepository->findTeamMembers($userContext->getSocieteUser());
            $isTeamUsers = true;
        }

        return $this->render('utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'societeUsers' => $societeUsers,
            'isTeamUsers' => $isTeamUsers
        ]);
    }

    /**
     * @Route("/utilisateur/{id}/modifier", name="app_fo_utilisateur_modifier")
     */
    public function modifier(
        Request $request,
        SocieteUser $societeUser,
        EntityManagerInterface $em
    )
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);

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
     *      "/utilisateur/{id}/desactiver",
     *      name="app_fo_utilisateur_disable",
     *      methods={"POST"}
     * )
     */
    public function disable(
        Request $request,
        SocieteUser $societeUser,
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        EnableDisableSocieteUserChecker $enableDisableSocieteUserChecker
    )
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);

        if (!$this->isCsrfTokenValid('disable_user_'.$societeUser->getId(), $request->get('csrf_token'))) {
            $this->addFlash('danger', $this->translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('app_fo_utilisateur_modifier', [
                'id' => $societeUser->getId(),
            ]);
        }

        if ($this->userContext->getSocieteUser() === $societeUser) {
            throw new ConflictHttpException($this->translator->trans('cannot_disable_self'));
        }

        if (!$societeUser->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur a déjà été désactivé.');
        }

        if (!$enableDisableSocieteUserChecker->canDisable($societeUser)){

            $this->addFlash('warning', $this->translator->trans('verif_date_leave_on_disable_user', [
                'user' => $societeUser->getUser()->getFullname(),
            ]));

            return $this->redirectToRoute('app_fo_utilisateur_modifier', [
                'id' => $societeUser->getId(),
            ]);
        }

        $societeUser->setEnabled(false);

        foreach ($societeUser->getProjetParticipants() as $projetParticipant){
            $dispatcher->dispatch(new ProjetParticipantRemovedEvent($em->getRepository(ProjetParticipant::class)->find($projetParticipant->getId())));
            $societeUser->removeProjetParticipant($projetParticipant);
            $em->remove($projetParticipant);
        }

        $em->persist($societeUser);
        $em->flush();

        $this->addFlash('warning', sprintf(
            'L\'utilisateur %s a été désactivé, il ne pourra plus se connecter.',
            $societeUser->getUser()->getFullname()
        ));

        return $this->redirectToRoute('app_fo_utilisateur_modifier', [
            'id' => $societeUser->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/utilisateur/{id}/activer",
     *      name="app_fo_utilisateur_enable",
     *      methods={"POST"}
     * )
     */
    public function enable(
        Request $request,
        SocieteUser $societeUser,
        EntityManagerInterface $em,
        EnableDisableSocieteUserChecker $enableDisableSocieteUserChecker
    )
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);

        if (!$this->isCsrfTokenValid('re_enable_user_'.$societeUser->getId(), $request->get('csrf_token'))) {
            $this->addFlash('danger', $this->translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('app_fo_utilisateur_modifier', [
                'id' => $societeUser->getId(),
            ]);
        }

        if ($societeUser->getEnabled()) {
            throw new ConflictHttpException('Cet utilisateur est déjà activé.');
        }

        if (!$enableDisableSocieteUserChecker->canEnable($societeUser)){

            $this->addFlash('warning', $this->translator->trans('verif_date_entry_on_enable_user', [
                'user' => $societeUser->getUser()->getFullname(),
            ]));

            return $this->redirectToRoute('app_fo_utilisateur_modifier', [
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

        return $this->redirectToRoute('app_fo_utilisateur_modifier', [
            'id' => $societeUser->getId(),
        ]);
    }

    /**
     * @Route("/utilisateur/{id}/supprimer", name="app_fo_utilisateur_delete")
     */
    public function delete(
        Request $request,
        SocieteUser $societeUser,
        EntityManagerInterface $em
    )
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);

        if ($request->isMethod('POST')) {

            if (!$this->isCsrfTokenValid('delete_user_'.$societeUser->getId(), $request->get('_token'))) {
                $this->addFlash('danger', $this->translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('app_fo_utilisateur_modifier', [
                    'id' => $societeUser->getId(),
                ]);
            }

            $user = $societeUser->getUser();

            if (!$user instanceof NullUser){
                $user->setCurrentSocieteUser(null);
                $em->persist($user);
            }

            $em->remove($societeUser);
            $em->flush();

            $this->addFlash('warning', $this->translator->trans('user_have_been_deleted', [
                'user' => $user->getFullname(),
            ]));

            return $this->redirectToRoute('app_fo_utilisateurs');
        }

        return $this->render('utilisateurs_fo/delete_user.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }

    /**
     * @Route(
     *      "/utilisateur/{id}/activite",
     *      name="app_fo_utilisateur_activity"
     * )
     */
    public function activity(
        SocieteUser $societeUser,
        EntityManagerInterface $em
    )
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);

        return $this->render('utilisateurs_fo/user_activity.html.twig', [
            'societeUser' => $societeUser,
            'activities' => $em->getRepository(SocieteUserActivity::class)->findBySocieteUser($societeUser),
        ]);
    }

}
