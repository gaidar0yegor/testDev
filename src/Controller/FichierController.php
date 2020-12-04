<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\FichierProjet;
use App\Form\FichierProjetType;
use App\ProjetResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FichiersProjetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
    public function uploadFichiers(Request $request, Projet $projet, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::CREATE, $projet);

        $fichierProjet = new FichierProjet();
        $form = $this->createForm(FichierProjetType::class, $fichierProjet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $fichierProjet->getFichier();
            $fileName = md5(uniqid()).'.'.$fichier->getFile()->guessExtension(); // uniqid = faire un Id unique

            $fichier
                ->setNomFichier($fichierProjet->getFichier()->getFile()->getClientOriginalName())
                ->setNomMd5($fileName)
                ->getFile()
                ->move($this->getParameter('upload_directory'), $fileName)
            ;

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
    public function delete(FichierProjet $fichierProjet, EntityManagerInterface $em, Projet $projet): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::DELETE, $fichierProjet);

        //path
        $chemin = $this->getParameter('upload_directory').'/'.$fichierProjet->getFichier()->getNomMd5();
        unlink($chemin);

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
    public function download(FichierProjet $fichierProjet): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::VIEW, $fichierProjet);

        $chemin = $this->getParameter('upload_directory').'/'.$fichierProjet->getFichier()->getNomMd5();

        $response = new BinaryFileResponse($chemin);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $fichierProjet->getFichier()->getNomFichier());
        return $response;
    }
}
