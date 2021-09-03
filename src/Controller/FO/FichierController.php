<?php

namespace App\Controller\FO;

use App\Entity\Projet;
use App\Entity\FichierProjet;
use App\Form\ProjetFichierProjetsType;
use App\ProjetResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\File\FileHandler\ProjectFileHandler;
use App\Form\FichierProjetRenameType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class FichierController extends AbstractController
{
    /**
     * @Route("/projets/{id}/fichiers", name="app_fo_projet_fichiers")
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

            return $this->redirectToRoute('app_fo_projet_fichiers', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('fichier/liste_fichiers.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
        ]);
    }

    /**
     * @Route("/projets/{projetId}/fichiers/{fichierProjetId}/rename", name="app_fo_projet_fichier_rename")
     */
    public function rename($projetId, $fichierProjetId, Request $request, EntityManagerInterface $em){

        // dd($fichierProjetId, $projetId);

        $data = $this->getDoctrine()->getRepository(FichierProjet ::class)->find($fichierProjetId);

        $form = $this->createForm(FichierProjetRenameType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_fo_projet_fichiers', [
                'id' => $projetId,
            ]);
        }

        

        return $this->render('fichier/rename_fichiers.html.twig', [
            'form' => $form->createView(),
            'id' => $projetId,
        ]);
    }

    /**
     * @Route("/projets/{projetId}/fichiers/{fichierProjetId}", name="app_fo_projet_fichier_delete", methods={"DELETE"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     */
    public function delete(
        FichierProjet $fichierProjet,
        ProjectFileHandler $projectFileHandler,
        EntityManagerInterface $em,
        Projet $projet
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::DELETE, $fichierProjet);

        $projectFileHandler->delete($fichierProjet->getFichier());

        $em->remove($fichierProjet);
        $em->flush();

        return $this->redirectToRoute('app_fo_projet_fichiers', [
            'id' => $projet->getid(),
        ]);
    }

    /**
     * @Route("/projets/{projetId}/fichiers/{fichierProjetId}", name="app_fo_projet_fichier", methods={"GET"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     */
    public function download(FichierProjet $fichierProjet, ProjectFileHandler $projectFileHandler): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::VIEW, $fichierProjet);

        return $projectFileHandler->createDownloadResponse($fichierProjet->getFichier());
    }
}
