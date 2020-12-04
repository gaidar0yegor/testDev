<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\FichierProjet;
use App\Form\FichierProjetType;
use App\ProjetResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FichiersProjetRepository;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FichierController extends AbstractController
{
    /**
     * @Route("/fiche/projet/{id}/liste/fichiers", name="liste_fichiers_")
     */
    public function listeFichiers(FichiersProjetRepository $fr, Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('fichier/liste_fichiers.html.twig', [
            'liste_fichiers' => $fr->findByProjet($projet),
            'canUploadFile' => $this->isGranted(ProjetResourceInterface::CREATE, $projet),
            'projet' => $projet,
        ]);
    }

    /**
     * @Route("/fiche/projet/{id}/ajout/fichier", name="ajout_fichier_")
     * @param Request $rq
     * @return Response
     */
    public function uploadFichiers(
        Request $request,
        Projet $projet,
        FilesystemInterface $defaultStorage,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::CREATE, $projet);

        $fichierProjet = new FichierProjet();
        $form = $this->createForm(FichierProjetType::class, $fichierProjet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $fichierProjet->getFichier();
            $fileName = md5(uniqid()).'.'.$fichier->getFile()->guessExtension();

            $fichier
                ->setNomFichier($fichier->getFile()->getClientOriginalName())
                ->setNomMd5($fileName)
            ;

            $stream = fopen($fichier->getFile()->getRealPath(), 'r+');
            $defaultStorage->writeStream("uploads/$fileName", $stream);
            fclose($stream);

            $fichierProjet
                ->setUploadedBy($this->getUser())
                ->setProjet($projet)
            ;

            $em->persist($fichierProjet);
            $em->flush();

            $this->addFlash('success', sprintf('Le fichier "%s" a été créé.', $fichier->getNomFichier()));

            return $this->redirectToRoute('liste_fichiers_', [
                'id' => $projet->getid(),
            ]);
        }

        return $this->render('fichier/infos_fichier.html.twig', [
            'form' => $form->createView(),
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
        FilesystemInterface $defaultStorage,
        EntityManagerInterface $em,
        Projet $projet
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::DELETE, $fichierProjet);

        $defaultStorage->delete('uploads/'.$fichierProjet->getFichier()->getNomMd5());

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
    public function download(FichierProjet $fichierProjet, FilesystemInterface $defaultStorage): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::VIEW, $fichierProjet);

        $stream = $defaultStorage->readStream('uploads/'.$fichierProjet->getFichier()->getNomMd5());

        return new StreamedResponse(function () use ($stream) {
            echo stream_get_contents($stream);
            flush();
        }, 200, [
            'Content-Transfer-Encoding', 'binary',
            'Content-Disposition' => sprintf(
                'attachment; filename="%s"',
                $fichierProjet->getFichier()->getNomFichier()
            ),
            'Content-Length' => fstat($stream)['size'],
        ]);
    }
}
