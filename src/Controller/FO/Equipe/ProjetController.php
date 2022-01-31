<?php

namespace App\Controller\FO\Equipe;

use App\Entity\FichierProjet;
use App\Entity\Projet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\File\FileHandler\ProjectFileHandler;
use App\HierarchicalSuperior\Security\Voter\ViewProjetHierarchicalSuperiorVoter;
use App\Repository\ProjetRepository;
use App\Repository\SocieteUserRepository;
use App\Service\SocieteChecker;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Security\Role\RoleProjet;
use App\MultiSociete\UserContext;
use App\Service\ParticipantService;

/**
 * @Route("/mon-equipe/projets")
 */
class ProjetController extends AbstractController
{
    /**
     * @Route("", name="app_fo_mon_equipe_projets")
     */
    public function listerProjet(
        UserContext $userContext,
        ProjetRepository $projetRepository,
        SocieteUserRepository $societeUserRepository
    ) {
        if (!$userContext->getSocieteUser()->isSuperiorFo()){
            throw new AccessDeniedException();
        }

        $societeUsers = $societeUserRepository->findTeamMembers($userContext->getSocieteUser());

        $projets = $projetRepository->findAllForUsers($societeUsers);
        $yearRange = $projetRepository->findProjetsYearRangeFor($userContext->getSocieteUser());

        return $this->render('projets/mon_equipe/list.html.twig', [
            'projets'=> $projets,
            'yearMin' => $yearRange['yearMin'] ?? date('Y'),
            'yearMax' => $yearRange['yearMax'] ?? date('Y'),
        ]);
    }

    /**
     * @Route("/{id}", name="app_fo_mon_equipe_projet", requirements={"id"="\d+"})
     */
    public function ficheProjet(
        Projet $projet,
        UserContext $userContext,
        ParticipantService $participantService,
        SocieteChecker $societeChecker
    ) {
        $this->denyAccessUnlessGranted(ViewProjetHierarchicalSuperiorVoter::VIEW, $projet);

        if ($participantService->isParticipant($userContext->getSocieteUser(), $projet)){
            return $this->redirectToRoute('app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/mon_equipe/fiche_projet.html.twig', [
            'projet' => $projet,
            'contributeurs' => $participantService->getProjetParticipantsWithRoleExactly(
                $projet->getActiveProjetParticipants(),
                RoleProjet::CONTRIBUTEUR
            )
        ]);
    }

    /**
     * @Route("/{projetId}/fichier/{fichierProjetId}", name="app_fo_mon_equipe_projet_view_file")
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     */
    public function viewFile(
        Projet $projet,
        FichierProjet $fichierProjet,
        ProjectFileHandler $projectFileHandler
    ) {
        $this->denyAccessUnlessGranted(ViewProjetHierarchicalSuperiorVoter::VIEW, $projet);

        if ($fichierProjet->getProjet() !== $projet) {
            throw $this->createAccessDeniedException();
        }

        return $projectFileHandler->createDownloadResponse($fichierProjet);
    }
}
