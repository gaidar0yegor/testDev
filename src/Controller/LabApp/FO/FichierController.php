<?php

namespace App\Controller\LabApp\FO;

use App\Entity\LabApp\Etude;
use App\Entity\LabApp\FichierEtude;
use App\EtudeResourceInterface;
use App\File\FileHandler\EtudeFileHandler;
use App\Form\LabApp\EtudeFichierEtudesType;
use App\Form\LabApp\FichierEtudeModifierType;
use Doctrine\ORM\EntityManagerInterface;
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
     * @Route("/etudes/{id}/fichiers", name="lab_app_fo_etude_fichiers")
     */
    public function listeFichiers(Request $request, Etude $etude, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('view', $etude);

        $form = $this->createForm(EtudeFichierEtudesType::class, $etude);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(EtudeResourceInterface::CREATE, $etude);

            $em->persist($etude);
            $em->flush();

            return $this->redirectToRoute('lab_app_fo_etude_fichiers', [
                'id' => $etude->getId(),
            ]);
        }

        return $this->render('lab_app/fichier/liste_fichiers.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }

    /**
     * @Route("/etudes/{etudeId}/fichiers/{fichierEtudeId}/modifier", name="lab_app_fo_etude_fichier_modifier")
     *
     * @ParamConverter("etude", options={"id" = "etudeId"})
     * @ParamConverter("fichierEtude", options={"id" = "fichierEtudeId"})
     */
    public function modifier(Etude $etude, FichierEtude $fichierEtude, Request $request, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EtudeResourceInterface::EDIT, $fichierEtude);

        $fichier = $fichierEtude->getFichier();

        $form = $this->createForm(FichierEtudeModifierType::class, $fichierEtude);

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

            return $this->redirectToRoute('lab_app_fo_etude_fichiers', [
                'id' => $etude->getId(),
            ]);
        }

        return $this->render('lab_app/fichier/modifier_fichiers.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }

    /**
     * @Route("/etudes/{etudeId}/fichiers/{fichierEtudeId}", name="lab_app_fo_etude_fichier_delete", methods={"DELETE"})
     *
     * @ParamConverter("etude", options={"id" = "etudeId"})
     * @ParamConverter("fichierEtude", options={"id" = "fichierEtudeId"})
     */
    public function delete(
        Etude $etude,
        FichierEtude $fichierEtude,
        EtudeFileHandler $etudeFileHandler,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(EtudeResourceInterface::DELETE, $fichierEtude);

        $etudeFileHandler->delete($fichierEtude);

        $em->remove($fichierEtude);
        $em->flush();

        return $this->redirectToRoute('lab_app_fo_etude_fichiers', [
            'id' => $etude->getid(),
        ]);
    }

    /**
     * @Route("/etudes/{etudeId}/fichiers/{fichierEtudeId}", name="lab_app_fo_etude_fichier", methods={"GET"})
     *
     * @ParamConverter("etude", options={"id" = "etudeId"})
     * @ParamConverter("fichierEtude", options={"id" = "fichierEtudeId"})
     */
    public function download(Request $request, FichierEtude $fichierEtude, EtudeFileHandler $etudeFileHandler): Response
    {
        $this->denyAccessUnlessGranted(EtudeResourceInterface::VIEW, $fichierEtude);

        return $etudeFileHandler->createDownloadResponse($fichierEtude, $request->query->has('download'));
    }
}
