<?php

namespace App\Controller\CorpApp;

use App\Exception\InvitationTokenExpiredException;
use App\Repository\ProjetObservateurExterneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ObervateurExterneController extends AbstractController
{
    /**
     * @Route("/invitation-observateur-externe/{token}", name="corp_app_observateur_externe_join")
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

        return $this->render('corp_app/projets/externe/join_projet.html.twig', [
            'projetObservateurExterne' => $projetObservateurExterne,
        ]);
    }

    /**
     * @Route("/invitation-observateur-externe/rejoindre-le-projet/{token}", name="corp_app_observateur_externe_join_confirm")
     *
     * @IsGranted("ROLE_FO_USER")
     */
    public function joinProjetConfirm(
        Request $request,
        string $token,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
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
                $this->addFlash('error', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('corp_app_observateur_externe_join_confirm', [
                    'token' => $token,
                ]);
            }

            $projetObservateurExterne
                ->setUser($this->getUser())
                ->removeInvitationToken()
            ;

            $em->flush();

            $this->addFlash('success', $translator->trans('Vous avez rejoint le projet !'));

            return $this->redirectToRoute('corp_app_fo_observateur_externe_view', [
                'id' => $projetObservateurExterne->getProjet()->getId(),
            ]);
        }

        return $this->render('corp_app/projets/externe/join_projet_confirm.html.twig', [
            'projetObservateurExterne' => $projetObservateurExterne,
        ]);
    }
}
