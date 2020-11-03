<?php

namespace App\Controller\FO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projet;
use App\Form\ProjetFormType;
use App\Repository\ProjetParticipantRepository;
use App\Entity\ProjetParticipant;
use App\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ProjetController extends AbstractController
{
    /**
     * @Route("/projets", name="projets_")
     */
    public function listerProjet(ProjetParticipantRepository $projetParticipantRepository)
    {
        return $this->render('projets/liste_projets.html.twig', [
            'participes'=> $projetParticipantRepository->findAllForUser($this->getUser()),
        ]);

    }

    /**
     * @Route("/infos_projet", name="infos_projet_")
     *
     * @IsGranted("ROLE_FO_CDP")
     */
    public function creation(Request $rq) : Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetFormType::class, $projet);

        $participant = new ProjetParticipant();
        $projet->addProjetParticipant($participant);
        $participant
            ->setUser($this->getUser())
            ->setRole(Role::CDP)
        ;

        $form->handleRequest($rq);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', sprintf('Le projet "%s" a été créé.', $projet->getTitre()));
            return $this->redirectToRoute('fiche_projet_', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/saisie_infos_projet.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/projets/{id}/edition", name="projet_edition")
     */
    public function edition(Request $request, Projet $projet): Response
    {
        $this->denyAccessUnlessGranted('edit', $projet);

        $form = $this->createForm(ProjetFormType::class, $projet);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', sprintf('Le projet "%s" a été modifié.', $projet->getTitre()));
            return $this->redirectToRoute('fiche_projet_', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/edition_projet.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fiche/projet/{id}", name="fiche_projet_")
     */
    public function ficheProjet(Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('projets/fiche_projet.html.twig', [
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet),
        ]);
    }

    /**
     * @Route("/liste/fichiers", name="liste_fichiers_")
     */
    // public function listeFichiers()
    // {
    //     $fichier = new Fichier();
    //     return $this->render('projets/liste_fichiers.html.twig', [
    //         'fichier' => $fichier,
    //     ]);
    // }
}
