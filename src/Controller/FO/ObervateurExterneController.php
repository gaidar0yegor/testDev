<?php

namespace App\Controller\FO;

use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\Entity\ProjetObservateurExterne;
use App\ObservateurExterne\Form\InviteObservateurExterne;
use App\ObservateurExterne\InvitationService;
use App\ObservateurExterne\Security\Voter\ViewProjetExterneVoter;
use App\Repository\ProjetObservateurExterneRepository;
use App\Security\Role\RoleProjet;
use App\Service\FichierService;
use App\Service\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ObervateurExterneController extends AbstractController
{
    /**
     * @Route("/projet-externe/{id}", name="app_fo_observateur_externe_view")
     */
    public function view(
        Projet $projet,
        ParticipantService $participantService
    ) {
        $this->denyAccessUnlessGranted(ViewProjetExterneVoter::VIEW, $projet);

        return $this->render('projets/externe/fiche_projet.html.twig', [
            'projet' => $projet,
            'contributeurs' => $participantService->getProjetParticipantsWithRoleExactly(
                $projet->getActiveProjetParticipants(),
                RoleProjet::CONTRIBUTEUR
            ),
        ]);
    }

    /**
     * @Route("/projet-externe/{projetId}/fichier/{fichierProjetId}", name="app_fo_observateur_externe_view_file")
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     */
    public function viewFile(
        Projet $projet,
        FichierProjet $fichierProjet,
        FichierService $fichierService
    ) {
        $this->denyAccessUnlessGranted(ViewProjetExterneVoter::VIEW, $projet);

        if ($fichierProjet->getProjet() !== $projet) {
            throw $this->createAccessDeniedException();
        }

        return $fichierService->createDownloadResponse($fichierProjet->getFichier());
    }

    /**
     * @Route("/projet-externe", name="app_fo_observateur_externe_list")
     */
    public function list(
        ProjetObservateurExterneRepository $projetObservateurExterneRepository
    ) {
        $projetObservateurExternes = $projetObservateurExterneRepository->findBy([
            'user' => $this->getUser(),
        ]);

        return $this->render('projets/externe/list.html.twig', [
            'projetObservateurExternes' => $projetObservateurExternes,
        ]);
    }

    /**
     * @Route("/projets/{id}/inviter-un-observateur-externe", name="app_fo_observateur_externe_invite")
     */
    public function invite(
        Request $request,
        Projet $projet,
        InvitationService $invitationService,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        $projetObservateurExterne = new ProjetObservateurExterne();
        $projetObservateurExterne->setProjet($projet);
        $form = $this->createForm(InviteObservateurExterne::class, $projetObservateurExterne);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitationService->sendInvitation($projetObservateurExterne);

            $em->persist($projetObservateurExterne);
            $em->flush();

            $this->addFlash(
                'success',
                'Une notification avec un lien d\'invitation a été envoyée à votre observateur externe.'
            );

            return $this->redirectToRoute('app_fo_projet_participant', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/externe/invite.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
        ]);
    }

    /**
     * @Route(
     *      "/projets/{projetId}/retirer-un-observateur-externe/{observateurId}",
     *      name="app_fo_observateur_externe_delete",
     *      methods={"POST"}
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetObservateurExterne", options={"id" = "observateurId"})
     */
    public function delete(
        Request $request,
        Projet $projet,
        ProjetObservateurExterne $projetObservateurExterne,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        if ($projetObservateurExterne->getProjet() !== $projet) {
            throw $this->createNotFoundException('Cet observateur n\'a pas d\'accès sur ce projet.');
        }

        $redirectResponse = $this->redirectToRoute('app_fo_projet_participant', [
            'id' => $projet->getId(),
        ]);

        if (!$this->isCsrfTokenValid('delete_observateur_externe', $request->get('csrf_token'))) {
            $this->addFlash('danger', 'Erreur, il semblerai que la page ait expiré, veuillez réessayer.');

            return $redirectResponse;
        }

        $this->addFlash('success', 'Cet observateur externe a été retiré.');

        $em->remove($projetObservateurExterne);
        $em->flush();

        return $redirectResponse;
    }
}
