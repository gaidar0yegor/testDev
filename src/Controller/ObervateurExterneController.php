<?php

namespace App\Controller;

use App\Exception\InvitationTokenExpiredException;
use App\Repository\ProjetObservateurExterneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ObervateurExterneController extends AbstractController
{
    /**
     * @Route("/invitation-observateur-externe/{token}", name="app_observateur_externe_join")
     */
    public function joinProjet(
        string $token,
        ProjetObservateurExterneRepository $projetObservateurExterneRepository
    ) {
        $projetObservateurExterne = $projetObservateurExterneRepository->findOneBy([
            'invitationToken' => $token,
        ]);

        if (null === $projetObservateurExterne) {
            throw new InvitationTokenExpiredException();
        }

        return $this->render('projets/externe/join_projet.html.twig', [
            'projetObservateurExterne' => $projetObservateurExterne,
        ]);
    }

    /**
     * @Route("/invitation-observateur-externe/rejoindre-le-projet/{token}", name="app_observateur_externe_join_confirm")
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function joinProjetConfirm(
        Request $request,
        string $token,
        EntityManagerInterface $em,
        ProjetObservateurExterneRepository $projetObservateurExterneRepository
    ) {
        $projetObservateurExterne = $projetObservateurExterneRepository->findOneBy([
            'invitationToken' => $token,
        ]);

        if (null === $projetObservateurExterne) {
            throw new InvitationTokenExpiredException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('invitation_join_projet_observateur_externe', $request->get('csrf_token'))) {
                $this->addFlash('danger', 'Erreur, il semblerai que le bouton ait expiré, veuillez réessayer.');

                return $this->redirectToRoute('app_fo_user_invitation_rejoindre', [
                    'token' => $token,
                ]);
            }

            $projetObservateurExterne
                ->setUser($this->getUser())
                ->removeInvitationToken()
            ;

            $em->flush();

            $this->addFlash('success', 'Vous avez rejoint le projet !');

            return $this->redirectToRoute('app_fo_observateur_externe_view', [
                'id' => $projetObservateurExterne->getProjet()->getId(),
            ]);
        }

        return $this->render('projets/externe/join_projet_confirm.html.twig', [
            'projetObservateurExterne' => $projetObservateurExterne,
        ]);
    }
}