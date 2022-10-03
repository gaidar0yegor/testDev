<?php

namespace App\Controller\CorpApp\FO\Equipe;

use App\Entity\SocieteUser;
use App\Form\SocieteUserProjetsRolesType;
use App\Security\Voter\SameSocieteVoter;
use App\Security\Voter\TeamManagementVoter;
use App\MultiSociete\UserContext;
use App\Service\UserProjetAffectation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/utilisateurs")
 */
class SocieteUserController extends AbstractController
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
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

        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);

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
