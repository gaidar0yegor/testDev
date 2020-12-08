<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\FichierProjet;
use App\Form\ProjetFichierProjetsType;
use App\ProjetResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FichierService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class FichierController extends AbstractController
{
    /**
     * @Route("/fiche/projet/{id}/liste/fichiers", name="liste_fichiers_")
     */
    public function listeFichiers(Request $request, Projet $projet, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        $form = $this->createForm(ProjetFichierProjetsType::class, $projet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(ProjetResourceInterface::CREATE, $projet);

            $em->persist($projet);
            $em->flush();

            return $this->redirectToRoute('liste_fichiers_', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('fichier/liste_fichiers.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
        ]);
    }

    /**
     * @Route("/fiche/projet/{projetId}/delete/fichier/{fichierProjetId}", name="efface_fichier_", methods={"DELETE"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     */
    public function delete(
        FichierProjet $fichierProjet,
        FichierService $fichierService,
        EntityManagerInterface $em,
        Projet $projet
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::DELETE, $fichierProjet);

        $fichierService->delete($fichierProjet->getFichier());

        $em->remove($fichierProjet);
        $em->flush();

        return $this->redirectToRoute('liste_fichiers_', [
            'id' => $projet->getid(),
        ]);
    }

    /**
     * @Route("/fiche/projet/{projetId}/dowload/fichier/{fichierProjetId}", name="telecharge_fichier_", methods={"GET"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     */
    public function download(FichierProjet $fichierProjet, FichierService $fichierService): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::VIEW, $fichierProjet);

        return $fichierService->createDownloadResponse($fichierProjet->getFichier());
    }
}
