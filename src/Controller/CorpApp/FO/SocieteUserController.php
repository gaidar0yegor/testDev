<?php

namespace App\Controller\CorpApp\FO;

use App\DTO\NullUser;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserPeriod;
use App\Exception\RdiException;
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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @Route("/utilisateur/{id}", name="corp_app_fo_societe_user")
     */
    public function compteUtilisateur(SocieteUser $societeUser)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        return $this->render('corp_app/utilisateurs_fo/view_user.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }

    /**
     * @Route("/utilisateurs", name="corp_app_fo_utilisateurs")
     */
    public function listerUtilisateurs(SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $userContext->getSocieteUser());

        if (!$this->isGranted(RoleSociete::ADMIN)){
            return $this->redirectToRoute('corp_app_fo_utilisateurs_team');
        }

        return $this->render('corp_app/utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'societeUsers' => $societeUserRepository->findBySameSociete($userContext->getSocieteUser()),
            'isTeamUsers' => false
        ]);
    }

    /**
     * @Route("/equipe/utilisateurs", name="corp_app_fo_utilisateurs_team")
     */
    public function listerEquipe(SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $userContext->getSocieteUser());

        return $this->render('corp_app/utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'societeUsers' => $societeUserRepository->findTeamMembers($userContext->getSocieteUser()),
            'isTeamUsers' => true
        ]);
    }

    /**
     * @Route("/utilisateur/{id}/modifier", name="corp_app_fo_utilisateur_modifier")
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

            return $this->redirectToRoute('corp_app_fo_societe_user', [
                'id' => $societeUser->getId(),
            ]);
        }

        return $this->render('corp_app/utilisateurs_fo/edit_user.html.twig', [
            'form' => $form->createView(),
            'societeUser' => $societeUser,
            'projetsAsCdp' => $projetsAsCdp,
            'putDateLeaveForDisabling' => $request->query->has('putDateLeaveForDisabling')
        ]);
    }

    /**
     * @Route(
     *      "/utilisateur/{id}/desactiver",
     *      name="corp_app_fo_utilisateur_disable",
     *      methods={"POST"}
     * )
     * @throws RdiException
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

        if ($this->isCsrfTokenValid('date_leave_disable_societe_user_'.$societeUser->getId(), $request->get('csrf_token'))) {
            $dateLeave = $request->get('dateLeave');

            if (!$dateLeave || !\DateTime::createFromFormat('d/m/Y', $dateLeave)) {
                throw new BadRequestHttpException('Date Leave is required');
            }

            if($societeUser->getSocieteUserPeriods()->last()->getDateLeave() === null){
                $societeUserPeriod = $societeUser->getSocieteUserPeriods()->last();
                $societeUserPeriod->setDateLeave(\DateTime::createFromFormat('d/m/Y', $dateLeave));
            } else {
                throw new RdiException('Une erreur est survenue !');
            }

            $em->persist($societeUser);
            $em->flush();

        } elseif (!$this->isCsrfTokenValid('disable_user_'.$societeUser->getId(), $request->get('csrf_token'))) {
            $this->addFlash('error', $this->translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('corp_app_fo_utilisateur_modifier', [
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

            return $this->redirectToRoute('corp_app_fo_utilisateur_modifier', [
                'id' => $societeUser->getId(),
                'putDateLeaveForDisabling' => true,
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

        return $this->redirectToRoute('corp_app_fo_utilisateur_modifier', [
            'id' => $societeUser->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/utilisateur/{id}/activer",
     *      name="corp_app_fo_utilisateur_enable",
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
            $this->addFlash('error', $this->translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('corp_app_fo_utilisateur_modifier', [
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

            return $this->redirectToRoute('corp_app_fo_utilisateur_modifier', [
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

        return $this->redirectToRoute('corp_app_fo_utilisateur_modifier', [
            'id' => $societeUser->getId(),
        ]);
    }

    /**
     * @Route("/utilisateur/{id}/supprimer", name="corp_app_fo_utilisateur_delete")
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
                $this->addFlash('error', $this->translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('corp_app_fo_utilisateur_modifier', [
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

            return $this->redirectToRoute('corp_app_fo_utilisateurs');
        }

        return $this->render('corp_app/utilisateurs_fo/delete_user.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }

    /**
     * @Route(
     *      "/utilisateur/{id}/activite",
     *      name="corp_app_fo_utilisateur_activity"
     * )
     */
    public function activity(
        SocieteUser $societeUser,
        EntityManagerInterface $em
    )
    {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);

        return $this->render('corp_app/utilisateurs_fo/user_activity.html.twig', [
            'societeUser' => $societeUser,
            'activities' => $em->getRepository(SocieteUserActivity::class)->findBySocieteUser($societeUser),
        ]);
    }

}
