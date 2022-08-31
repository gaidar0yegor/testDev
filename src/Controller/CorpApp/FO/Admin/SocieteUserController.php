<?php

namespace App\Controller\CorpApp\FO\Admin;

use App\DTO\NullUser;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Form\InviteUserType;
use App\Form\SocieteUserProjetsRolesType;
use App\Form\SocieteUserType;
use App\Notification\Event\ProjetParticipantRemovedEvent;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/utilisateurs")
 */
class SocieteUserController extends AbstractController
{
    /**
     * @Route("", name="corp_app_fo_admin_utilisateurs")
     */
    public function listerUtilisateurs(SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        return $this->render('corp_app/utilisateurs_fo/liste_utilisateurs_fo.html.twig', [
            'societeUsers' => $societeUserRepository->findBySameSociete($userContext->getSocieteUser()),
        ]);
    }

    /**
     * @Route("/invite", name="corp_app_fo_admin_user_invite")
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

            return $this->redirectToRoute('corp_app_fo_admin_user_invite');
        }

        return $this->render('corp_app/utilisateurs_fo/invite_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *      "/{id}/roles-projets",
     *      name="corp_app_fo_admin_utilisateur_roles_projets"
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

            return $this->redirectToRoute('corp_app_fo_admin_utilisateur_roles_projets', [
                'id' => $societeUser->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash(
                'error',
                'Les rôles n\'ont pas été mis à jour à cause d\'une incohérence, vous pouvez revérifier'
            );
        }

        return $this->render('corp_app/utilisateurs_fo/roles_projets.html.twig', [
            'societeUser' => $societeUser,
            'form' => $form->createView(),
        ]);
    }
}
