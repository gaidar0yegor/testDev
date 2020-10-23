<?php

namespace App\Controller\Fo;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projet;
use App\Form\ProjetFormType;
use App\Repository\ProjetParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function saisieInfosProjet(Request $rq) : Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetFormType::class, $projet);

        $form->handleRequest($rq);

        if($form->isSubmitted() && $form->isValid()) {
            $projet->setChefDeProjet($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', sprintf('Le projet "%s" a été créé.', $projet->getTitre()));
            return $this->redirectToRoute('projets_');
        }

        return $this->render('projets/saisie_infos_projet.html.twig', [
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
