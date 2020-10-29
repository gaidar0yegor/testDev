<?php

namespace App\Controller\FO;

use App\Entity\Projet;
use App\Form\ListeProjetParticipantsType;
use App\Service\SocieteChecker;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjetParticipantController extends AbstractController
{
    /**
     * @Route("/projet/{projetId}/participants", name="projet_participant")
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function index(
        Request $request,
        Projet $projet,
        SocieteChecker $societeChecker,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        $form = $this->createForm(ListeProjetParticipantsType::class, $projet);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Empêche de faire basculer le projet dans une autre société
            if (!$societeChecker->isSameSociete($projet->getChefDeProjet(), $this->getUser())) {
                throw $this->createAccessDeniedException();
            }

            $em->flush();

            return $this->redirectToRoute('fiche_projet_', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/participants/index.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }
}
