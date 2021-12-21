<?php

namespace App\Controller\FO;

use App\Entity\Projet;
use App\Entity\FichierProjet;
use App\Form\ProjetFichierProjetsType;
use App\Notification\Event\FichierProjetAddedEvent;
use App\ProjetResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\File\FileHandler\ProjectFileHandler;
use App\Form\FichierProjetRenameType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FichierController extends AbstractController
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

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

            $this->dispatcher->dispatch(new FichierProjetAddedEvent($projet->getFichierProjets()->last()));

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
    public function rename($projetId, $fichierProjetId, Request $request, EntityManagerInterface $em, TranslatorInterface $translator){

        $fichierProjet = $this->getDoctrine()->getRepository(FichierProjet ::class)->find($fichierProjetId);

        $this->denyAccessUnlessGranted(ProjetResourceInterface::EDIT, $fichierProjet);

        $fichier = $fichierProjet->getFichier();

        $form = $this->createForm(FichierProjetRenameType::class, $fichierProjet);

        $oldName = $fichier->getNomFichier();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nomMd5 = $form->get('fichier')->getData()->getNomMd5();
    
            $originalExt = pathinfo($nomMd5, PATHINFO_EXTENSION);

            $nomFichier = $form->get('fichier')->getData()->getNomFichier();
    
            $wrongExt = pathinfo($nomFichier, PATHINFO_EXTENSION);

            $newName = pathinfo($nomFichier, PATHINFO_FILENAME);

            if($originalExt !== $wrongExt){
                $fichier->setNomFichier($newName . '.' . $originalExt);
            }

            $em->flush();     

            $this->addFlash('success', $translator->trans('edit_file_success', [
                'oldName' => $oldName,
                'newName' => $fichier->getNomFichier(),
            ]));

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
